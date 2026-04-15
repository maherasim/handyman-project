<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\ProviderPayout;
use App\Models\BookingHandymanMapping;
use App\Models\HandymanPayout;
use App\Models\HandymanType;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;
use App\Models\CommissionEarning;
use App\Models\Payment;

class EarningController extends Controller
{
    public function index()
    {
        $earningScope = 'all';
        $pageTitle = __('messages.earnings');
        return view('earning.index', compact('pageTitle', 'earningScope'));
    }

    public function bookingEarning()
    {
        $earningScope = 'booking';
        $pageTitle = __('messages.earnings') . ' — ' . __('messages.booking');
        return view('earning.index', compact('pageTitle', 'earningScope'));
    }

    public function postJobEarning()
    {
        $earningScope = 'post_job';
        $pageTitle = __('messages.earnings') . ' — ' . __('messages.post_job_request');
        return view('earning.index', compact('pageTitle', 'earningScope'));
    }

    public function setEarningData(Request $request)
    {
        $scope = $request->input('scope', 'all');
        if (!in_array($scope, ['all', 'booking', 'post_job'], true)) {
            $scope = 'all';
        }

        $query = User::select('users.*')
            ->with('commission_earning')
            ->whereHas('commission_earning', function ($q) use ($scope) {
                $q->whereIn('commission_status', ['paid', 'unpaid'])
                    ->where('user_type', 'provider');
                if ($scope === 'booking') {
                    $q->whereNotNull('booking_id');
                } elseif ($scope === 'post_job') {
                    $q->whereNull('booking_id')
                        ->whereNotNull('post_job_bid_request_id')
                        ->where('post_job_bid_request_id', '>', 0);
                }
            })->orderBy('updated_at', 'desc');

        $providers = $query->get();

        // DataTables server-side sends `draw`; do not rely on X-Requested-With alone (some proxies/CDNs strip it).
        $isDataTablesRequest = $request->ajax() || $request->has('draw');

        if ($isDataTablesRequest) {
            return Datatables::of($query)
                ->addIndexColumn()

                ->addColumn('provider_name', function ($row) {
                    $user_id = $row->id;
                    $user_name = $row->display_name;
                    $user_image = getSingleMedia(optional($row), 'profile_image', null);
                    $email = $row->email;
                    return view('earning.user', compact('row', 'user_id', 'user_name', 'email', 'user_image'));
                })

                ->orderColumn('provider_name', function ($query, $order) use ($scope) {
                    $query->join('commission_earnings', 'commission_earnings.employee_id', '=', 'users.id')
                        ->whereIn('commission_earnings.commission_status', ['unpaid', 'paid'])
                        ->where('commission_earnings.user_type', 'provider');
                    if ($scope === 'booking') {
                        $query->whereNotNull('commission_earnings.booking_id');
                    } elseif ($scope === 'post_job') {
                        $query->whereNull('commission_earnings.booking_id')
                            ->whereNotNull('commission_earnings.post_job_bid_request_id')
                            ->where('commission_earnings.post_job_bid_request_id', '>', 0);
                    }
                    $query->groupBy('users.id')
                        ->orderBy('users.display_name', $order);
                })

                ->addColumn('action', function ($row) use ($scope) {
                    $btn = '-';
                    $provider_id = $row->id;

                    $providerUnpaidQuery = CommissionEarning::where('employee_id', $provider_id)
                        ->where('user_type', 'provider')
                        ->where('commission_status', 'unpaid');
                    if ($scope === 'booking') {
                        $providerUnpaidQuery->whereNotNull('booking_id');
                    } elseif ($scope === 'post_job') {
                        $providerUnpaidQuery->whereNull('booking_id')
                            ->whereNotNull('post_job_bid_request_id')
                            ->where('post_job_bid_request_id', '>', 0);
                    }
                    $providerUnpaid = $providerUnpaidQuery->get();

                    $bookingIds = $providerUnpaid->pluck('booking_id')->filter()->unique()->values();
                    $postJobIds = $providerUnpaid->pluck('post_job_bid_request_id')->filter()->unique()->values();

                    $providerUnpaidSum = $providerUnpaid->sum('commission_amount');

                    $handymanUnpaidForBookings = $bookingIds->isNotEmpty()
                        ? CommissionEarning::whereIn('booking_id', $bookingIds)
                            ->where('user_type', 'handyman')
                            ->where('commission_status', 'unpaid')
                            ->sum('commission_amount')
                        : 0;

                    $handymanUnpaidForPostJobs = $postJobIds->isNotEmpty()
                        ? CommissionEarning::whereIn('post_job_bid_request_id', $postJobIds)
                            ->whereNull('booking_id')
                            ->where('post_job_bid_request_id', '>', 0)
                            ->where('user_type', 'handyman')
                            ->where('commission_status', 'unpaid')
                            ->sum('commission_amount')
                        : 0;

                    $row['total_pay'] = $providerUnpaidSum + $handymanUnpaidForBookings + $handymanUnpaidForPostJobs;

                    if ($providerUnpaid->count() > 0) {
                        $btn = "<a href=" . route('providerpayout.create', $provider_id) . "><i class='fas fa-money-bill-alt earning-icon'></i></a>";
                    }

                    return $btn;
                })

                ->editColumn('total_bookings', function ($row) use ($scope) {
                    $baseQuery = $row->commission_earning()
                        ->whereIn('commission_status', ['unpaid', 'paid'])
                        ->where('user_type', 'provider');

                    $bookingCount = (clone $baseQuery)
                        ->whereNotNull('booking_id')
                        ->distinct('booking_id')
                        ->count('booking_id');

                    $postJobCount = (clone $baseQuery)
                        ->whereNull('booking_id')
                        ->whereNotNull('post_job_bid_request_id')
                        ->where('post_job_bid_request_id', '>', 0)
                        ->distinct('post_job_bid_request_id')
                        ->count('post_job_bid_request_id');

                    if ($scope === 'booking') {
                        return $bookingCount > 0
                            ? "<b><a href='" . route('booking.index', ['provider_id' => $row->id]) . "' class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='View Provider Bookings'>{$bookingCount}</a></b>"
                            : "<b><span class='text-primary text-nowrap px-1'>0</span></b>";
                    }
                    if ($scope === 'post_job') {
                        return $postJobCount > 0
                            ? "<b><span class='text-primary text-nowrap px-1' title='Post Job Requests'>{$postJobCount}</span></b>"
                            : "<b><span class='text-primary text-nowrap px-1'>0</span></b>";
                    }

                    if ($bookingCount === 0 && $postJobCount > 0) {
                        return "<b><span class='text-primary text-nowrap px-1' title='Post Job Requests'>{$postJobCount}</span></b>";
                    }

                    $totalBookings = $bookingCount + $postJobCount;
                    $row['total_bookings'] = $totalBookings;

                    return $totalBookings > 0
                        ? "<b><a href='" . route('booking.index', ['provider_id' => $row->id]) . "' class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='View Provider Bookings'>{$totalBookings}</a></b>"
                        : "<b><span class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='View Provider Bookings'>0</span>";
                })

                ->editColumn('total_earning', function ($row) use ($scope) {
                    // Post job: provider share only — sum commission_earnings for this provider + bid
                    if ($scope === 'post_job') {
                        $amount = CommissionEarning::query()
                            ->where('employee_id', $row->id)
                            ->where('user_type', 'provider')
                            ->whereNull('booking_id')
                            ->whereNotNull('post_job_bid_request_id')
                            ->where('post_job_bid_request_id', '>', 0)
                            ->whereIn('commission_status', ['paid', 'unpaid'])
                            ->sum('commission_amount');

                        return getPriceFormat($amount);
                    }

                    $providerPaidQuery = CommissionEarning::where('employee_id', $row->id)
                        ->where('user_type', 'provider')
                        ->where('commission_status', 'paid');
                    if ($scope === 'booking') {
                        $providerPaidQuery->whereNotNull('booking_id');
                    }
                    $providerPaid = $providerPaidQuery->get();

                    $bookingIds = $providerPaid->pluck('booking_id')->filter()->unique()->values();
                    $postJobIds = $providerPaid->pluck('post_job_bid_request_id')->filter()->unique()->values();

                    if ($bookingIds->isEmpty() && $postJobIds->isEmpty()) {
                        return getPriceFormat(0);
                    }

                    $totalEarning = CommissionEarning::where('commission_status', 'paid')
                        ->whereIn('user_type', ['provider', 'admin', 'handyman'])
                        ->where(function ($query) use ($bookingIds, $postJobIds) {
                            if ($bookingIds->isNotEmpty() && $postJobIds->isNotEmpty()) {
                                $query->where(function ($q) use ($bookingIds) {
                                    $q->whereIn('booking_id', $bookingIds);
                                })->orWhere(function ($q) use ($postJobIds) {
                                    $q->whereIn('post_job_bid_request_id', $postJobIds)
                                        ->whereNull('booking_id')
                                        ->where('post_job_bid_request_id', '>', 0);
                                });
                            } elseif ($bookingIds->isNotEmpty()) {
                                $query->whereIn('booking_id', $bookingIds);
                            } elseif ($postJobIds->isNotEmpty()) {
                                $query->whereIn('post_job_bid_request_id', $postJobIds)
                                    ->whereNull('booking_id')
                                    ->where('post_job_bid_request_id', '>', 0);
                            }
                        })
                        ->sum('commission_amount');

                    return getPriceFormat($totalEarning);
                })

                ->editColumn('admin_earning', function ($row) use ($scope) {
                    $providerCommissionsQuery = CommissionEarning::where('employee_id', $row->id)
                        ->where('user_type', 'provider');
                    if ($scope === 'booking') {
                        $providerCommissionsQuery->whereNotNull('booking_id');
                    } elseif ($scope === 'post_job') {
                        $providerCommissionsQuery->whereNull('booking_id')
                            ->whereNotNull('post_job_bid_request_id')
                            ->where('post_job_bid_request_id', '>', 0);
                    }
                    $providerCommissions = $providerCommissionsQuery->get();

                    $bookingIds = $providerCommissions->pluck('booking_id')->filter()->unique()->values();
                    $postJobIds = $providerCommissions->pluck('post_job_bid_request_id')->filter()->unique()->values();

                    if ($scope !== 'all' && $bookingIds->isEmpty() && $postJobIds->isEmpty()) {
                        return getPriceFormat(0);
                    }

                    $totalAdminEarning = CommissionEarning::where('user_type', 'admin')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->where(function ($query) use ($bookingIds, $postJobIds, $scope) {
                            if ($bookingIds->isNotEmpty()) {
                                $query->whereIn('booking_id', $bookingIds);
                            }
                            if ($postJobIds->isNotEmpty()) {
                                $query->orWhereIn('post_job_bid_request_id', $postJobIds);
                            }
                            if ($scope === 'all') {
                                $query->orWhereNull('booking_id');
                            }
                        })
                        ->sum('commission_amount');
                    return getPriceFormat($totalAdminEarning);
                })

                ->editColumn('provider_earning', function ($row) {
                    return getPriceFormat($row->total_pay ?? 0);
                })

                ->editColumn('handyman_total_earning', function ($row) use ($scope) {
                    $providerCommissionsQuery = CommissionEarning::where('employee_id', $row->id)
                        ->where('user_type', 'provider');
                    if ($scope === 'booking') {
                        $providerCommissionsQuery->whereNotNull('booking_id');
                    } elseif ($scope === 'post_job') {
                        $providerCommissionsQuery->whereNull('booking_id')
                            ->whereNotNull('post_job_bid_request_id')
                            ->where('post_job_bid_request_id', '>', 0);
                    }
                    $providerCommissions = $providerCommissionsQuery->get();

                    $bookingIds = $providerCommissions->pluck('booking_id')->filter()->unique()->values();
                    $postJobIds = $providerCommissions->pluck('post_job_bid_request_id')->filter()->unique()->values();

                    $handymanEarning = 0;
                    if ($bookingIds->isNotEmpty()) {
                        $handymanEarning += CommissionEarning::whereIn('booking_id', $bookingIds)
                            ->where('user_type', 'handyman')
                            ->whereIn('commission_status', ['paid', 'unpaid'])
                            ->sum('commission_amount');
                    }
                    if ($postJobIds->isNotEmpty()) {
                        $handymanEarning += CommissionEarning::whereIn('post_job_bid_request_id', $postJobIds)
                            ->whereNull('booking_id')
                            ->where('post_job_bid_request_id', '>', 0)
                            ->where('user_type', 'handyman')
                            ->whereIn('commission_status', ['paid', 'unpaid'])
                            ->sum('commission_amount');
                    }

                    return getPriceFormat($handymanEarning);
                })


                ->editColumn('provider_paid_earning', function ($row) {
                    $paid = ProviderPayout::where('provider_id', $row->id)->sum('amount');
                    return "<b><a href='" . route('providerpayout.show', $row->id) . "' class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='" . __('messages.view_provider_payout') . "'>" . getPriceFormat($paid) . "</a></b>";
                })

                ->rawColumns([
                    'provider_name',
                    'action',
                    'total_bookings',
                    'commission',
                    'total_earning',
                    'provider_total_earning',
                    'provider_paid_earning',
                    'handyman_total_earning'
                ])
                ->make(true);
        }

        if ($request->is('api/*')) {
            $earningData = [];

            foreach ($providers as $provider) {
                // Step 1: Get all booking_ids for this provider with completed status
                $bookingIds = CommissionEarning::where('employee_id', $provider->id)
                    ->where('user_type', 'provider')
                    ->whereHas('getbooking', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->pluck('booking_id');

                // Step 2: Total earning (sum of all commissions from provider, admin, handyman)
                $totalEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('commission_status', 'paid')
                    ->whereIn('user_type', ['provider', 'admin', 'handyman'])
                    ->sum('commission_amount');

                // ✅ Step 3: Admin earning (only user_type = 'admin')
                $adminEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('user_type', 'admin')
                    ->whereIn('commission_status', ['paid', 'unpaid'])
                    ->sum('commission_amount');

                // Step 4: Provider due amount (unpaid commissions for provider & handyman)
                $providerDueAmount = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('commission_status', 'unpaid')
                    ->whereIn('user_type', ['provider', 'handyman'])
                    ->sum('commission_amount');

                // Step 5: Handyman paid earnings
                $handyman_total_earning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('user_type', 'handyman')
                    ->where('commission_status', 'paid')
                    ->sum('commission_amount');

                // Step 6: Total bookings count
                $totalBookings = $bookingIds->unique()->count();

                // Step 7: Provider paid earning (payouts)
                $provider_paid_earning = ProviderPayout::where('provider_id', $provider->id)->sum('amount');

                // Step 8: Total taxes
                $totalTax = Booking::whereIn('id', $bookingIds)->sum('final_total_tax');

                $earningData[] = [
                    'provider_id' => $provider->id,
                    'provider_name' => $provider->display_name,
                    'provider_image' => getSingleMedia(optional($provider), 'profile_image', null),
                    'email' => $provider->email,
                    'commission' => optional($provider->providertype)->commission,
                    'commission_type' => optional($provider->providertype)->type,
                    'total_bookings' => $totalBookings,
                    'total_earning' => $totalEarning,
                    'taxes' => $totalTax,
                    'taxes_formate' => getPriceFormat($totalTax, 2),
                    'admin_earning' => $adminEarning,
                    'provider_paid_earning' => $provider_paid_earning,
                    'provider_paid_earning_formate' => getPriceFormat($provider_paid_earning),
                    'provider_due_amount' => $providerDueAmount,
                    'handyman_total_amount' => $handyman_total_earning,
                ];
            }

            return comman_custom_response($earningData);
        }
    }






    public function handymanEarning()
    {
        $pageTitle =  __('messages.earning');
        return view('earning.handyman', compact('pageTitle'));
    }
    public function handymanEarningData(Request $request)
    {
        $auth_user = authSession();

        $query = User::select('users.*')
            ->with('commission_earning')
            ->whereHas('commission_earning', function ($q) {
                $q->whereIn('commission_status', ['paid', 'unpaid'])
                    ->where('user_type', 'handyman');
            })->orderBy('updated_at', 'desc');

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->withTrashed();
        }
        if (auth()->user()->hasAnyRole(['provider'])) {
            $query->where('provider_id', auth()->user()->id);
        }

        if ($request->ajax() || $request->has('draw')) {
            return Datatables::of($query)
                ->addIndexColumn()

                ->addColumn('handyman_name', function ($row) {
                    $user_id = $row->id;
                    $user_name = $row->display_name;
                    $user_image = getSingleMedia(optional($row), 'profile_image', null);
                    $email = $row->email;
                    return view('earning.user', compact('row', 'user_id', 'user_name', 'email', 'user_image'));
                })

                ->addColumn('action', function ($row) {
                    $btn = '-';
                    $handyman_id = $row->id;

                    $commissionData = $row->commission_earning()
                        ->whereHas('getbooking', function ($query) {
                            $query->where('status', 'completed');
                        })
                        ->where('commission_status', 'unpaid')
                        ->where('user_type', 'handyman');

                    $commissionAmount = $commissionData->sum('commission_amount');

                    $row['total_pay'] = $commissionAmount;
                    $row['commission'] = $commissionData->get();

                    if ($commissionData->count() > 0) {
                        $btn = "<a href=" . route('handymanpayout.create', $handyman_id) . "><i class='fas fa-money-bill-alt earning-icon'></i></a>";
                    }

                    return $btn;
                })

                ->editColumn('total_bookings', function ($row) {
                    $commissionData = $row->commission_earning()
                        ->whereHas('getbooking', function ($query) {
                            $query->where('status', 'completed');
                        })
                        ->whereIn('commission_status', ['unpaid', 'paid'])
                        ->where('user_type', 'handyman');

                    $totalBookings = $commissionData->distinct('booking_id')->count();
                    $row['total_bookings'] = $totalBookings;

                    if ($row->total_bookings > 0) {
                        return "<b><a href='" . route('booking.index', ['handyman_id' => $row->id]) . "' data-assign-module='" . $row->id . "'  class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='View Handyman Bookings'>" . $row->total_bookings . "</a> </b>";
                    } else {
                        return "<b><span  data-assign-module='" . $row->id . "'  class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='View Handyman Bookings'>0</span>";
                    }
                })

                ->editColumn('total_earning', function ($row) {
                    $bookingIds = $row->commission_earning()
                        ->where('user_type', 'handyman')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->whereHas('getbooking', function ($q) {
                            $q->where('status', 'completed');
                        })
                        ->pluck('booking_id');

                    // Sum actual payment amounts (advanced_paid + remaining payment)
                    $totalServiceAmount = Payment::whereIn('booking_id', $bookingIds)
                        ->whereIn('payment_status', ['advanced_paid', 'paid'])
                        ->sum('total_amount');

                    return getPriceFormat($totalServiceAmount);
                })

                ->editColumn('admin_earning', function ($row) {
                    $bookingIds = $row->commission_earning()
                        ->where('user_type', 'handyman')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->whereHas('getbooking', function ($q) {
                            $q->where('status', 'completed');
                        })
                        ->pluck('booking_id');

                    $totalAdminEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                        ->where('user_type', 'admin')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->sum('commission_amount');

                    return getPriceFormat($totalAdminEarning);
                })

                ->editColumn('handyman_earning', function ($row) {
                    $bookingIds = $row->commission_earning()
                        ->where('user_type', 'handyman')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->whereHas('getbooking', function ($q) {
                            $q->where('status', 'completed');
                        })
                        ->pluck('booking_id');

                    $totalHandymanEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                        ->where('user_type', 'handyman')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->sum('commission_amount');

                    return getPriceFormat($totalHandymanEarning);
                })

                ->editColumn('handyman_paid_earning', function ($row) {
                    $commissionData = HandymanPayout::where('handyman_id', $row->id)
                        ->sum('amount');
                    $handyman_paid_earning = $commissionData ?? 0;

                    return "<b><a href='" . route('handymanpayout.show', $row->id) . "' data-assign-module='" . $row->id . "'  class='text-primary text-nowrap px-1' data-bs-toggle='tooltip' title='" . __('messages.view_handyman_payout') . "'>" . getPriceFormat($handyman_paid_earning) . "</a> </b>";
                })

                ->editColumn('provider_earning', function ($row) {
                    $bookingIds = $row->commission_earning()
                        ->where('user_type', 'handyman')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->whereHas('getbooking', function ($q) {
                            $q->where('status', 'completed');
                        })
                        ->pluck('booking_id');

                    $provider_earning = CommissionEarning::whereIn('booking_id', $bookingIds)
                        ->where('user_type', 'provider')
                        ->whereIn('commission_status', ['paid', 'unpaid'])
                        ->sum('commission_amount');

                    return $provider_earning ? getPriceFormat($provider_earning) : getPriceFormat(0);
                })

                ->rawColumns(['action', 'total_earning', 'total_bookings', 'provider_earning', 'handyman_paid_earning'])
                ->make(true);
        }

        if ($request->is('api/*')) {
            if (auth()->user()->hasAnyRole(['provider'])) {
                $handymen_list = $query->where('provider_id', auth()->user()->id)->get();
            } else {
                $handymen_list = $query->get();
            }

            $handymanearningData = [];

            foreach ($handymen_list as $handyman) {
                $commissionData = $handyman->commission_earning()
                    ->whereHas('getbooking', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->whereIn('commission_status', ['unpaid', 'paid'])
                    ->where('user_type', 'handyman')
                    ->get();

                $bookingIds = $commissionData->pluck('booking_id');

                $totalBookings = $commissionData->unique('booking_id')->count();

                // $totalEarning = Booking::whereIn('id', $bookingIds)->sum('final_sub_total');
                $totalEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('commission_status', 'paid')
                    ->whereIn('user_type', ['provider', 'admin', 'handyman'])
                    ->sum('commission_amount');
                $adminEarning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('user_type', 'admin')
                    ->whereIn('commission_status', ['paid', 'unpaid'])
                    ->sum('commission_amount');

                $provider_total_earning = CommissionEarning::whereIn('booking_id', $bookingIds)
                    ->where('user_type', 'provider')
                    ->whereIn('commission_status', ['paid', 'unpaid'])
                    ->sum('commission_amount');

                $handymanCommissionData = $handyman->commission_earning()
                    ->whereHas('getbooking', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->where('commission_status', 'unpaid')
                    ->where('user_type', 'handyman')->get();

                $handymanDueAmount = 0;
                if ($handymanCommissionData->count() > 0) {
                    foreach ($handymanCommissionData as $commission) {
                        if ($commission != null) {
                            $handyman_commission_data = CommissionEarning::where('booking_id', $commission->booking_id)
                                ->whereIn('user_type', ['handyman'])
                                ->where('commission_status', 'unpaid')
                                ->get();

                            if ($handyman_commission_data) {
                                foreach ($handyman_commission_data as $data) {
                                    if (isset($data->commission_amount)) {
                                        $handymanDueAmount += $data->commission_amount;
                                    }
                                }
                            }
                        }
                    }
                }

                $handyman_paid_earning = HandymanPayout::where('handyman_id', $handyman->id)->sum('amount') ?? 0;

                $handymanearningData[] = [
                    'handyman_id' => $handyman->id,
                    'handyman_name' => $handyman->display_name,
                    'handyman_image' => getSingleMedia(optional($handyman), 'profile_image', null),
                    'email' => $handyman->email,
                    'commission' => optional($handyman->providertype)->commission,
                    'commission_type' => optional($handyman->providertype)->type,
                    'total_bookings' => $totalBookings,
                    'total_earning' => $totalEarning,
                    'admin_earning' => $adminEarning,
                    'handyman_paid_earning' => $handyman_paid_earning,
                    'handyman_paid_earning_formate' => $handyman_paid_earning,
                    'handyman_due_amount' => $handymanDueAmount,
                    'provider_total_amount' => $provider_total_earning,
                ];
            }
            return comman_custom_response($handymanearningData);
        }
    }



    public function show($id)
    {
        //
        $user = User::where('id', $id)->first();
        $auth_user = authSession();
        $assets = ['datatable'];

        if ($user->user_type == 'provider') {
            $pageTitle = __('messages.list_form_title', ['form' => __('messages.providerpayout_list')]);
            $providerdata = $user;
            return view('providerpayout.view', compact('pageTitle', 'auth_user', 'assets', 'id', 'providerdata'));
        } else if ($user->user_type == 'handyman') {
            $pageTitle = __('messages.list_form_title', ['form' => __('messages.handymanpayout')]);
            if ($user->provider_id == auth()->user()->id) {
                $handymandata = $user;
                return view('handymanpayout.view', compact('pageTitle', 'auth_user', 'assets', 'handymandata'));
            }
            return redirect(route('handyman.index'))->withErrors(trans('messages.demo_permission_denied'));
        }
    }
}
