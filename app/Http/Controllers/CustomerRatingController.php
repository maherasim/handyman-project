<?php

namespace App\Http\Controllers;

use App\Models\CustomerRating;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerRatingController extends Controller
{
    /**
     * Employer (provider) → customer ratings from `customer_ratings` (booking flow).
     * Admin: all rows. Customer (user_type user): only ratings they received.
     */
    public function index(Request $request)
    {
        $this->authorizeList();

        $pageTitle = __('messages.customer_received_ratings_title');
        $auth_user = authSession();
        $assets = ['datatable'];
        $filter = [
            'status' => $request->status,
        ];
        $isAdmin = in_array(auth()->user()->user_type, ['admin', 'demo_admin'], true);

        return view('customerrating.index', compact('pageTitle', 'auth_user', 'assets', 'filter', 'isAdmin'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $this->authorizeList();

        $query = CustomerRating::query()
            ->with(['provider', 'customer', 'booking'])
            ->listForBackend();

        $filter = $request->filter;
        if (isset($filter) && isset($filter['column_status'])) {
            $query->where('status', $filter['column_status']);
        }

        $isAdmin = in_array(auth()->user()->user_type, ['admin', 'demo_admin'], true);

        $dt = $datatable->eloquent($query);

        if ($isAdmin) {
            $dt->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
            })
                ->editColumn('customer_id', function ($row) {
                    return view('customerrating.customer', ['query' => $row])->render();
                })
                ->filterColumn('customer_id', function ($q, $keyword) {
                    $q->whereHas('customer', function ($sub) use ($keyword) {
                        $sub->where('display_name', 'like', '%'.$keyword.'%')
                            ->orWhere('email', 'like', '%'.$keyword.'%');
                    });
                })
                ->orderColumn('customer_id', function ($q, $order) {
                    $q->select('customer_ratings.*')
                        ->join('users as rated_customers', 'rated_customers.id', '=', 'customer_ratings.customer_id')
                        ->orderBy('rated_customers.display_name', $order);
                });
        }

        $dt = $dt
            ->editColumn('provider_id', function ($row) {
                return view('customerrating.provider', ['query' => $row])->render();
            })
            ->filterColumn('provider_id', function ($q, $keyword) {
                $q->whereHas('provider', function ($sub) use ($keyword) {
                    $sub->where('display_name', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%');
                });
            })
            ->orderColumn('provider_id', function ($q, $order) {
                $q->select('customer_ratings.*')
                    ->join('users as employers', 'employers.id', '=', 'customer_ratings.provider_id')
                    ->orderBy('employers.display_name', $order);
            })
            ->editColumn('booking_id', function ($row) {
                $bid = $row->booking_id;

                return $bid
                    ? '<a href="'.route('booking.details', $bid).'">#'.$bid.'</a>'
                    : '—';
            })
            ->editColumn('review', function ($row) {
                return ($row->review !== null && $row->review !== '') ? $row->review : '—';
            });

        if ($isAdmin) {
            $dt->addColumn('action', function ($row) {
                return view('customerrating.action', ['customerrating' => $row])->render();
            });
        }

        return $dt
            ->addIndexColumn()
            ->rawColumns(array_merge(
                ['provider_id', 'booking_id'],
                $isAdmin ? ['check', 'customer_id', 'action'] : []
            ))
            ->toJson();
    }

    public function bulk_action(Request $request)
    {
        if (! in_array(auth()->user()->user_type, ['admin', 'demo_admin'], true)) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'delete':
                CustomerRating::listForBackend()->whereIn('id', $ids)->delete();
                $message = __('messages.msg_deleted', ['name' => __('messages.customer_received_ratings_title')]);

                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function destroy($id)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo.permission.denied'));
        }

        if (! in_array(auth()->user()->user_type, ['admin', 'demo_admin'], true)) {
            return comman_custom_response(['message' => 'Forbidden', 'status' => false]);
        }

        $rating = CustomerRating::listForBackend()->where('id', $id)->first();
        $msg = __('messages.msg_fail_to_delete', ['name' => __('messages.customer_received_ratings_title')]);

        if ($rating) {
            $rating->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.customer_received_ratings_title')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    protected function authorizeList(): void
    {
        $t = auth()->user()->user_type;
        if (in_array($t, ['user', 'admin', 'demo_admin'], true)) {
            return;
        }

        abort(403);
    }
}
