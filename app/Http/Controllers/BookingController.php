<?php

namespace App\Http\Controllers;

use App\Exports\BookingExport;
use App\Http\Requests\BookingUpdateRequest;
use App\Http\Resources\API\ServiceProofResource;
use App\Models\AppSetting;
use App\Models\Booking;
use App\Models\BookingHandymanMapping;
use App\Models\HandymanPayout;
use App\Models\BookingRating;
use App\Models\HandymanRating;
use App\Models\CustomerRating;
use App\Models\BookingStatus;
use App\Models\PaymentHistory;
use App\Models\CommissionEarning;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PostJobRequest;
use App\Models\ProviderAddressMapping;
use App\Models\ProviderPayout;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Models\ServiceProof;
use App\Models\ServiceSlot;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceBookingNotificationMail;
use App\Models\Wallet;
use App\Traits\EarningTrait;
use App\Traits\NotificationTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\DataTables;

class BookingController extends Controller
{
    use NotificationTrait;
    use EarningTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.bookings');
        $auth_user = authSession();
        $authRole = $auth_user->roles->pluck('name')->first();
        $assets = ['datatable'];

        $provider_id = '';
        $handyman_id = '';
        $customer_id = '';

        // Calculate total earnings based on role
        $advanceFilter = [];
        $paymentStatus = ['paid', 'pending', 'advanced_paid', 'Advanced Refund'];
        $paymentType = PaymentGateway::where('status', 1)->get()->pluck('title', 'type')->put('wallet', 'Wallet');
        $bookingStatus = BookingStatus::where('status', 1)->orderBy('sequence', 'ASC')->get()->pluck('label', 'value');
        switch ($authRole) {
            case 'admin':
                $totalEarning = Booking::where('status', '!=', 'cancelled')
                    ->whereHas('handymanAdded', function ($query) {
                        $query->whereNotNull('provider_id'); // Ensure handyman is not null
                    })
                    ->sum('total_amount');
                break;
            case 'demo_admin':
                $totalEarning = Booking::where('status', '!=', 'cancelled')
                    ->whereHas('handymanAdded', function ($query) {
                        $query->whereNotNull('provider_id'); // Ensure handyman is not null
                    })
                    ->sum('total_amount');
                break;


            case 'provider':
                $totalEarning = Booking::where('status', '!=', 'cancelled')->whereHas('handymanAdded', function ($query) use ($auth_user) {
                    $query->where('provider_id', $auth_user->id);
                })->sum('total_amount');
                break;


            case 'handyman':
                $totalEarning = Booking::where('status', '!=', 'cancelled')->whereHas('handymanAdded', function ($query) use ($auth_user) {
                    $query->where('handyman_id', $auth_user->id);
                })->sum('total_amount');
                break;

            default:
                $totalEarning = 0;
                break;
        }

        $advanceFilter = [
            'paymentStatus' => ['paid', 'pending', 'advanced_paid', 'Advanced Refund'],
            'paymentType' => PaymentGateway::where('status', 1)->get()->pluck('title', 'type')->put('wallet', 'Wallet'),
            'bookingStatus' => BookingStatus::where('status', 1)->orderBy('sequence', 'ASC')->get()->pluck('label', 'value')
        ];

        return view('booking.index', compact('pageTitle', 'auth_user', 'assets', 'filter', 'customer_id', 'provider_id', 'handyman_id', 'advanceFilter', 'totalEarning'));
    }


    public function index_data(DataTables $datatable, Request $request)
    {
        $auth_user = authSession();
        $query = Booking::query()->myBooking()->with('payment', 'commissionsdata', 'handymanAdded', 'service.city', 'service.country');

        // Apply role-based filters
        if ($auth_user->hasRole('handyman')) {
            $query->whereHas('handymanAdded', function ($q) use ($auth_user) {
                $q->where('handyman_id', $auth_user->id);
            });
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
            if (isset($filter['status'])) {
                $query->where('status', $filter['status']);
            }
        }

        // Handle advanceFilter
        $advanceFilter = $request->advanceFilter;

        if (isset($advanceFilter)) {
            // Date range filter - Updated logic
            if (!empty($advanceFilter['date_range'])) {
                $dates = explode(' to ', $advanceFilter['date_range']);
                if (count($dates) === 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $query->whereDate('date', '>=', $startDate)
                        ->whereDate('date', '<=', $endDate);
                } elseif (count($dates) === 1) {
                    $date = date('Y-m-d', strtotime($dates[0]));
                    $query->whereDate('date', $date);
                }
            }

            // Other filters...
            $filters = [
                'customer_id' => 'customer_id',
                'provider_id' => 'provider_id',
                'service_id' => 'service_id',
                'handyman_id' => ['handymanAdded', 'handyman_id'],
                'booking_status' => 'status',
                'payment_status' => ['payment', 'payment_status'],
                'payment_type' => ['payment', 'payment_type'],
            ];

            foreach ($filters as $key => $filter) {
                if (!empty($advanceFilter[$key])) {
                    if (is_array($advanceFilter[$key]) && count($advanceFilter[$key]) > 0) {
                        if (is_array($filter)) {
                            $query->whereHas($filter[0], function ($subQuery) use ($filter, $advanceFilter, $key) {
                                $subQuery->whereIn($filter[1], $advanceFilter[$key]);
                            });
                        } else {
                            $query->whereIn($filter, $advanceFilter[$key]);
                        }
                    } elseif (!is_array($advanceFilter[$key]) && $advanceFilter[$key] !== '') {
                        $query->where($filter, $advanceFilter[$key]);
                    }
                }
            }
        }

        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->withTrashed();
        }

        return $datatable->eloquent($query)
            // ->addColumn('check', function ($row) {
            //     return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="booking" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            // })
            ->editColumn('id', function ($query) {
                return "#" . $query->id;
            })
            ->editColumn('customer_id', function ($query) {
                return view('booking.customer', compact('query'));
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('customer_id', function ($query, $order) {
                $query->select('bookings.*')
                    ->join('users as customers', 'customers.id', '=', 'bookings.customer_id')
                    ->orderBy('customers.display_name', $order);
            })
            ->editColumn('service_id', function ($query) {
                if (!empty($query->bookingPackage)) {
                    $name = optional($query->bookingPackage)->name;
                } else {
                    $name = optional($query->service)->name;
                }
                $service_name = ($query->service_id != null && isset($query->service)) ? $name : "";
                return $service_name;
            })
            ->filterColumn('service_id', function ($query, $keyword) {
                $query->whereHas('service', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('service_id', function ($query, $order) {
                $query->join('services', 'services.id', '=', 'bookings.service_id')
                    ->orderBy('services.name', $order);
            })
            ->editColumn('date', function ($query) {
                $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                $date = optional($datetime)->date_format && optional($datetime)->time_format
                    ? date(optional($datetime)->date_format, strtotime($query->date)) . '  ' . date(optional($datetime)->time_format, strtotime($query->date))
                    : $query->date;

                return $date;
            })
            ->editColumn('provider_id', function ($query) {
                return view('booking.provider', compact('query'));
            })
            ->filterColumn('provider_id', function ($query, $keyword) {
                $query->whereHas('service', function ($q) use ($keyword) {
                    $q->whereHas('city', function ($cityQuery) use ($keyword) {
                        $cityQuery->where('name', 'like', '%' . $keyword . '%');
                    })->orWhereHas('country', function ($countryQuery) use ($keyword) {
                        $countryQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                });
            })
            ->orderColumn('provider_id', function ($query, $order) {
                $query->select('bookings.*')
                    ->join('services', 'services.id', '=', 'bookings.service_id')
                    ->leftJoin('cities', 'cities.id', '=', 'services.city_id')
                    ->leftJoin('countries', 'countries.id', '=', 'services.country_id')
                    ->orderByRaw("CONCAT(COALESCE(countries.name, ''), '-', COALESCE(cities.name, '')) {$order}");
            })
            // ->editColumn('status', function ($query) {
            //     return bookingstatus(BookingStatus::bookingStatus($query->status));
            // })
            ->editColumn('status', function ($query) {
                $statusKey = strtolower((string) $query->status);
                $statusMap = [
                    'pending' => ['Pending', 'badge bg-dark text-white'],
                    'accept' => ['Accepted', 'badge bg-info text-white'],
                    'on_going' => ['On Going', 'badge bg-primary text-white'],
                    'in_progress' => ['In Progress', 'badge bg-primary text-white'],
                    'pending_approval' => ['Pending Approval', 'badge bg-warning text-dark'],
                    'confirm' => ['Confirmed', 'badge bg-success text-white'],
                    'hold' => ['On Hold', 'badge bg-warning text-dark'],
                    'completed' => ['Completed', 'badge bg-success text-white'],
                    'cancelled' => ['Cancelled', 'badge bg-danger text-white'],
                    'rejected' => ['Rejected', 'badge bg-danger text-white'],
                ];

                if (isset($statusMap[$statusKey])) {
                    [$label, $classes] = $statusMap[$statusKey];
                } else {
                    $label = str_replace('_', ' ', ucfirst((string) $query->status));
                    $classes = 'badge bg-secondary text-white';
                }

                return '<span class="' . $classes . '">' . e($label) . '</span>';
            })

            ->editColumn('payment_id', function ($query) {
                // If booking is cancelled, force payment status to 'cancelled'
                $payment_status = null;
                if ($query->status === 'cancelled') {
                    $payment_status = 'cancelled';
                } else {
                    $payment = $query->payment()->orderBy('id', 'desc')->first();
                    $payment_status = $payment ? $payment->payment_status : null;
                }

                if ($payment_status !== null) {
                    $badgeClass = $payment_status === 'cancelled' ? 'bg-danger' : 'bg-primary';
                    $status = '<span class="text-center text-white badge ' . $badgeClass . '">' . str_replace('_', " ", ucfirst($payment_status)) . '</span>';
                } else {
                    $status = '<span class="badge bg-primary text-white">' . __('messages.pending') . '</span>';
                }
                return $status;
            })
            ->filterColumn('payment_id', function ($query, $keyword) {
                $query->whereHas('payment', function ($q) use ($keyword) {
                    $q->where('payment_status', 'like', $keyword . '%');
                });
            })
            ->editColumn('total_amount', function ($query) {
                return $query->total_amount ? getPriceFormat($query->total_amount) : '-';
            })
            ->addColumn('view_booking', function ($booking) {
                $url = route('booking.show', $booking->id);
                return '<a href="' . e($url) . '" class="btn btn-sm btn-primary" title="' . e(__('messages.view')) . '"><i class="ri-eye-line"></i></a>';
            })
            ->addColumn('action', function ($booking) {
                return view('booking.action', compact('booking'))->render();
            })
            ->editColumn('updated_at', function ($query) {
                $diff = Carbon::now()->diffInHours($query->updated_at);
                if ($diff < 25) {
                    return $query->updated_at->diffForHumans();
                } else {
                    return $query->updated_at->isoFormat('llll');
                }
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'view_booking', 'status', 'payment_id', 'service_id', 'id'])
            ->toJson();
    }


    public function export(Request $request)
    {
        // Get the selected format (default to 'xlsx')
        $format = $request->input('format', 'xlsx');

        // Validate format
        $validFormats = ['xlsx', 'xls', 'ods', 'csv', 'pdf', 'html'];
        if (!in_array($format, $validFormats)) {
            return response()->json(['error' => 'Invalid format selected.'], 400);
        }

        // Get the selected columns (passed as JSON array)
        $columns = json_decode($request->input('columns'), true);

        // Create a new instance of BookingExport with role-based filtering
        $auth_user = authSession();

        // Modify the query based on user role
        $query = Booking::query()->with(['service', 'customer', 'provider', 'payment', 'handymanAdded']);

        // Apply role-based filters
        switch ($auth_user->roles->pluck('name')->first()) {
            case 'handyman':
                $query->whereHas('handymanAdded', function ($q) use ($auth_user) {
                    $q->where('handyman_id', $auth_user->id);
                });
                break;

            case 'provider':
                $query->where('provider_id', $auth_user->id);
                break;

            case 'user':
                $query->where('customer_id', $auth_user->id);
                break;
        }

        // Apply any additional filters from the request
        if ($request->has('advanceFilter')) {
            $advanceFilter = $request->advanceFilter;

            // Apply filters similar to index_data method
            if (!empty($advanceFilter['customer_id'])) {
                $query->whereIn('customer_id', $advanceFilter['customer_id']);
            }
            if (!empty($advanceFilter['service_id'])) {
                $query->whereIn('service_id', $advanceFilter['service_id']);
            }
            if (!empty($advanceFilter['booking_status'])) {
                $query->whereIn('status', $advanceFilter['booking_status']);
            }
            if (!empty($advanceFilter['payment_status'])) {
                $query->whereHas('payment', function ($q) use ($advanceFilter) {
                    $q->whereIn('payment_status', $advanceFilter['payment_status']);
                });
            }
            if (!empty($advanceFilter['payment_type'])) {
                $query->whereHas('payment', function ($q) use ($advanceFilter) {
                    $q->whereIn('payment_type', $advanceFilter['payment_type']);
                });
            }
            if (!empty($advanceFilter['date_range'])) {
                $dates = explode(' to ', $advanceFilter['date_range']);
                if (count($dates) === 2) {
                    $query->whereDate('date', '>=', $dates[0])
                        ->whereDate('date', '<=', $dates[1]);
                } elseif (count($dates) === 1) {
                    $query->whereDate('date', $dates[0]);
                }
            }
        }

        // Check if there's any data to export
        $bookingData = $query->get();
        if ($bookingData->isEmpty()) {
            return response()->json(['error' => 'No data found for export.'], 404);
        }

        // Create BookingExport instance with the filtered query
        $bookingExport = new BookingExport($columns, $query);

        // Set the filename based on the selected format
        $filename = 'bookings.' . $format;

        // Handle PDF format specifically
        if ($format === 'pdf') {
            return Excel::download($bookingExport, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        // Handle other formats (xlsx, xls, ods, csv, html)
        return Excel::download($bookingExport, $filename, constant('\Maatwebsite\Excel\Excel::' . strtoupper($format)));
    }

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_action_updated');
        switch ($actionType) {
            case 'change-status':
                $branches = Booking::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_booking_status_updated');
                break;

            case 'delete':
                $bookings = Booking::whereIn('id', $ids)->get();
                foreach ($bookings as $booking) {
                    $booking->delete();
                }

                $message = __('messages.bulk_booking_deleted');
                break;

            case 'restore':
                $bookings = Booking::withTrashed()->whereIn('id', $ids)->get();
                foreach ($bookings as $booking) {
                    $booking->restore();
                }
                $message = __('messages.bulk_booking_restored');
                break;

            case 'permanently-delete':
                $bookings = Booking::withTrashed()->whereIn('id', $ids)->get();
                foreach ($bookings as $booking) {
                    $booking->forceDelete();
                }
                $message = __('messages.bulk_booking_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('messages.action_invalid')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $bookingdata = Booking::find($id);
        $pageTitle = __('messages.update_form_title', ['form' => __('messages.booking')]);

        if ($bookingdata == null) {
            $pageTitle = __('messages.add_button_form', ['form' => __('messages.booking')]);
            $bookingdata = new Booking;
        }

        return view('booking.create', compact('pageTitle', 'bookingdata', 'auth_user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $admin = json_decode($sitesetup->value);
        date_default_timezone_set($admin->time_zone ?? 'UTC');
        $data = $request->all();

        $data['tax'] = null;
        if ($request->id == null) {
            $data['status'] = !empty($data['status']) ? $data['status'] : 'pending';
        }

        if (isset($data['booking_slot'])) {
            $date = isset($request->date) ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');
            $time = date('H:i:s', strtotime($data['booking_slot']));
            $data['date'] = $date . ' ' . $time;
        } else {
//            $data['date'] = isset($request->date) ? date('Y-m-d H:i:s', strtotime($request->date)) : date('Y-m-d H:i:s');
            $data['date'] = Carbon::now();
        }

        $service_data = Service::find($data['service_id']);

        $data['provider_id'] = !empty($data['provider_id']) ? $data['provider_id'] : $service_data->provider_id;

        if ($request->has('tax') && $request->tax != null) {
            $data['tax'] = json_encode($request->tax);
        }

        $data['start_at'] = $request->start_at;
        $data['end_at'] = $request->end_at;

        if ($request->coupon_id != null) {
            $coupons = Coupon::with('serviceAdded')->where('code', $request->coupon_id)
                ->where('expire_date', '>', date('Y-m-d H:i'))->where('status', 1)
                ->whereHas('serviceAdded', function ($coupon) use ($service_data) {
                    $coupon->where('service_id', $service_data->id);
                })->first();
            if ($coupons == null) {
                return comman_message_response(__('messages.invalid_coupon_code'), 406);
            } else {
                $data['coupon_id'] = $coupons->id;
            }
        }

        $user = User::where('id', $data['provider_id'])->with('providertype')->first();

        // BUG-1: reject duplicate slot before persisting anything
        if (empty($request->id)) {
            foreach ($request->schedule_slots as $slot) {
                $conflict = ServiceSlot::whereHas('booking', function ($q) use ($data) {
                    $q->where('provider_id', $data['provider_id'])
                      ->whereNotIn('status', ['cancelled', 'rejected', 'completed']);
                })->where('date', $slot['date'])
                  ->where('start_time', $slot['start_time'])
                  ->exists();

                if ($conflict) {
                    return comman_message_response(__('messages.slot_already_booked'), 409);
                }
            }
        }

        $result = Booking::updateOrCreate(['id' => $request->id], $data);

        foreach ($request->schedule_slots as $slot) {
            ServiceSlot::create([
                'booking_id' => $result->id,
                'date' => $slot['date'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'] ?? null,
                'total_days' => $slot['total_days'],
                'total_hours' => $slot['total_hours'],
            ]);
        }

        $activity_data = [
            'activity_type' => 'add_booking',
            'booking_id' => $result->id,
            'booking' => $result,
        ];
        $this->sendNotification($activity_data);
        
        // Send email notification to provider about new booking
        try {
            // Only send email if this is a new booking (not an update)
            if ($result->wasRecentlyCreated) {
                $result->load(['service', 'customer', 'slots']);
                $provider = User::find($result->provider_id);
                $customer = User::find($result->customer_id);
                
                if ($provider && $provider->email && $customer) {
                    Mail::to($provider->email)->locale(getRecipientLocale($provider))->send(new ServiceBookingNotificationMail($provider, $result, $customer, getRecipientLocale($provider))); // *** new: locale-aware email ***
                    \Log::info('Service booking notification email sent to provider: ' . $provider->email . ' for booking ID: ' . $result->id);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the booking creation
            \Log::error('Failed to send service booking notification email: ' . $e->getMessage());
        }


        if ($data['coupon_id'] != null) {
            $coupons = Coupon::find($data['coupon_id']);

            $coupon_data = [
                'booking_id' => $result->id,
                'code' => $coupons->code,
                'discount' => $coupons->discount,
                'discount_type' => $coupons->discount_type,
            ];

            $result->couponAdded()->create($coupon_data);
        }
        if ($request->has('booking_address_id') && $request->booking_address_id != null) {
            $booking_address_mapping = ProviderAddressMapping::find($data['booking_address_id']);

            $booking_address_data = [
                'booking_id' => $result->id,
                'address' => $booking_address_mapping->address,
                'latitude' => $booking_address_mapping->latitude,
                'longitude' => $booking_address_mapping->longitude,
            ];

            $result->addressAdded()->create($booking_address_data);
        } elseif ($request->filled('address') && $request->filled('latitude') && $request->filled('longitude')) {
            $booking_address_data = [
                'booking_id' => $result->id,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];
            $result->addressAdded()->create($booking_address_data);
        }

        if ($request->has('service_addon_id') && is_array($request->service_addon_id) != null) {
            foreach ($request->service_addon_id as $serviceaddon) {
                $booking_serviceaddon_mapping = ServiceAddon::find($serviceaddon);
                if ($booking_serviceaddon_mapping) {
                    $booking_serviceaddon_data = [
                        'booking_id' => $result->id,
                        'service_addon_id' => $booking_serviceaddon_mapping->id,
                        'name' => $booking_serviceaddon_mapping->name,
                        'price' => $booking_serviceaddon_mapping->price,
                    ];

                    $result->bookingAddonService()->create($booking_serviceaddon_data);
                }
            }
        }


        if ($request->has('booking_package') && $request->booking_package != null) {
            $booking_package = [
                'booking_id' => $result->id,
                'service_package_id' => $data['booking_package']['id'],
                'provider_id' => $data['provider_id'],
                'name' => $data['booking_package']['name'],
                'is_featured' => $data['booking_package']['is_featured'],
                'package_type' => $data['booking_package']['package_type'],
                'price' => $data['booking_package']['price'],
            ];
            if (!empty($data['booking_package']['start_at'])) {
                $booking_package['start_at'] = $data['booking_package']['start_at'];
            }
            if (!empty($data['booking_package']['end_at'])) {
                $booking_package['end_at'] = $data['booking_package']['end_at'];
            }
            if (!empty($data['booking_package']['subcategory_id'])) {
                $booking_package['subcategory_id'] = $data['booking_package']['subcategory_id'];
            }
            if (!empty($data['booking_package']['category_id'])) {
                $booking_package['category_id'] = $data['booking_package']['category_id'];
            }
            if (!empty($data['booking_package']['service_id'])) {

                $serviceIds = explode(',', $data['booking_package']['service_id']);

                $services = [];

                foreach ($serviceIds as $serviceId) {
                    $service = Service::find($serviceId);

                    if ($service) {
                        $services[] = [
                            'service_id' => $service->id,
                            'price' => $service->price,
                        ];
                    }
                }

                $booking_package['services'] = json_encode($services);
            }
            $result->bookingPackage()->create($booking_package);
        }
        if (!empty($data['type']) && $data['type'] === 'user_post_job') {
            $post_request = PostJobRequest::where('id', $data['post_request_id'])->first();
            $post_request->date = isset($request->date) ? date('Y-m-d H:i:s', strtotime($request->date)) : date('Y-m-d H:i:s');
            $post_request->update();
        }
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.booking')]);
        }

        if ($request->is('api/*')) {
            $response = [
                'message' => $message,
                'booking_id' => $result->id
            ];
            return comman_custom_response($response);
        }
        return redirect(route('booking.index'))->withSuccess($message);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $auth_user = authSession();

        $user = auth()->user();
        $user->last_notification_seen = now();
        $user->save();

        if (count($user->unreadNotifications) > 0) {

            foreach ($user->unreadNotifications as $notifications) {

                $dataId = $notifications['data']['id'] ?? null;
                if ($dataId != null && $dataId == $id) {

                    $notification = $user->unreadNotifications->where('id', $notifications['id'])->first();
                    if ($notification) {
                        $notification->markAsRead();
                    }
                }

            }

        }


        $bookingdata = Booking::with(['bookingExtraCharge', 'payment', 'service', 'handymanAdded.handyman', 'customer', 'provider'])->myBooking()->find($id);
 

        $tabpage = 'info';
        if (empty($bookingdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.booking')]);
            return redirect(route('booking.index'))->withError($msg);
        }
        if (count($auth_user->unreadNotifications) > 0) {
            $auth_user->unreadNotifications->where('data.id', $id)->markAsRead();
        }

        // Check if customer rating already exists for this booking
        $customer_rating_exists = CustomerRating::where('booking_id', $id)->exists();

        $pageTitle = __('messages.view_form_title', ['form' => __('messages.booking')]);
        return view('booking.view', compact('pageTitle', 'bookingdata', 'auth_user', 'tabpage', 'customer_rating_exists'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $auth_user = authSession();

        $bookingdata = Booking::myBooking()->find($id);

        $pageTitle = __('messages.update_form_title', ['form' => __('messages.booking')]);
        $relation = [
            'status' => BookingStatus::where('status', 1)->orderBy('sequence', 'ASC')->get()->pluck('label', 'value'),
        ];
        return view('booking.edit', compact('pageTitle', 'bookingdata', 'auth_user') + $relation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(BookingUpdateRequest $request, $id)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();


        $data['date'] = isset($request->date) ? date('Y-m-d H:i:s', strtotime($request->date)) : date('Y-m-d H:i:s');
        $data['start_at'] = isset($request->start_at) ? date('Y-m-d H:i:s', strtotime($request->start_at)) : null;
        $data['end_at'] = isset($request->end_at) ? date('Y-m-d H:i:s', strtotime($request->end_at)) : null;


        $bookingdata = Booking::find($id);
        $paymentdata = Payment::where('booking_id', $id)->first();
        if ($data['status'] === 'hold') {
            if ($bookingdata->start_at == null && $bookingdata->end_at == null) {
                $duration_diff = duration($data['start_at'], $data['end_at'], 'in_minute');
                $data['duration_diff'] = $duration_diff;
            } else {
                if ($bookingdata->status == $data['status']) {
                    $booking_start_date = $bookingdata->start_at;
                    $request_start_date = $data['start_at'];
                    if ($request_start_date > $booking_start_date) {
                        $msg = __('messages.already_in_status', ['status' => $data['status']]);
                        return redirect()->back()->withSuccess($msg);
                    }
                } else {
                    $duration_diff = $bookingdata->duration_diff;
                    $new_diff = duration($bookingdata->start_at, $bookingdata->end_at, 'in_minute');
                    $data['duration_diff'] = $duration_diff + $new_diff;
                }
            }
        }
        if ($bookingdata->status != $data['status']) {
            $activity_type = 'update_booking_status';
        }
        if ($data['status'] == 'cancelled') {
            $activity_type = 'cancel_booking';
        }
        $data['reason'] = isset($data['reason']) ? $data['reason'] : null;
        $old_status = $bookingdata->status;

        $user = AUth::user();

        $data['user'] = $user;

        $bookingdata->update($data);
        if ($old_status != $data['status']) {
            // Reload booking to get fresh data after update with all relationships
            $bookingdata->refresh();
            $bookingdata->load(['customer', 'provider', 'service', 'handymanAdded.handyman', 'payment']);
            $bookingdata->old_status = $old_status;

            $activity_data = [
                'activity_type' => $activity_type,
                'booking_id' => $id,
                'booking' => $bookingdata,
            ];
            
            try {
            $this->sendNotification($activity_data);
                // Ensure database notification is created immediately (fallback if queue fails)
                $this->createDirectDatabaseNotification($bookingdata, $activity_type, $old_status, $data['status']);
            } catch (\Exception $e) {
                \Log::error('Failed to send booking notification (web): ' . $e->getMessage());
                // Fallback: create direct database notification even if sendNotification fails
                try {
                    $this->createDirectDatabaseNotification($bookingdata, $activity_type, $old_status, $data['status']);
                } catch (\Exception $fallbackError) {
                    \Log::error('Failed to create fallback database notification (web): ' . $fallbackError->getMessage());
                }
            }
            
            // ✅ Send direct email notifications to relevant parties
            try {
                $actor = auth()->user();
                $actorName = $actor ? ($actor->display_name ?? $actor->first_name ?? 'System') : 'System';
                $actorType = 'system';
                
                if ($actor) {
                    if ($actor->hasAnyRole(['provider']) && $actor->id == $bookingdata->provider_id) {
                        $actorType = 'provider';
                    } elseif ($actor->hasAnyRole(['handyman'])) {
                        $actorType = 'handyman';
                    } elseif ($actor->hasAnyRole(['user']) && $actor->id == $bookingdata->customer_id) {
                        $actorType = 'user';
                    }
                }
                
                $newStatus = $data['status'];
                
                // Determine who should receive emails based on who performed the action
                $emailsToSend = [];
                
                if ($actorType === 'handyman') {
                    // Handyman action: notify provider and user
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                } elseif ($actorType === 'provider') {
                    // Provider action: notify user and handyman
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                } elseif ($actorType === 'user') {
                    // User action: notify provider and handyman
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                } else {
                    // System/admin action: notify all parties
                    if ($bookingdata->provider && $bookingdata->provider->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->provider,
                            'type' => 'provider'
                        ];
                    }
                    if ($bookingdata->customer && $bookingdata->customer->email) {
                        $emailsToSend[] = [
                            'user' => $bookingdata->customer,
                            'type' => 'user'
                        ];
                    }
                    if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                        foreach ($bookingdata->handymanAdded as $handymanMapping) {
                            if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                                $emailsToSend[] = [
                                    'user' => $handymanMapping->handyman,
                                    'type' => 'handyman'
                                ];
                            }
                        }
                    }
                }
                
                // Emails sent via CommonNotification (DB template)
            } catch (\Exception $e) {
                \Log::error('Failed to send booking status update emails (web): ' . $e->getMessage(), [
                    'booking_id' => $id,
                    'trace' => $e->getTraceAsString()
                ]);
            }

        }
        if ($bookingdata->payment_id != null) {
            $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
            $paymentdata->update($data);

            if ($bookingdata->payment_id != null) {
                $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
                $paymentdata->update($data);

                $activity_data = [
                    'activity_type' => 'payment_message_status',
                    'payment_status' => $data['payment_status'],
                    'booking_id' => $id,
                    'booking' => $bookingdata,
                ];
                $this->sendNotification($activity_data);

            }
        }
        $message = __('messages.update_form', ['form' => __('messages.booking')]);

        if ($request->is('api/*')) {

            return comman_message_response($message);
        }

        return redirect(route('booking.index'))->withSuccess($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $booking = Booking::find($id);

        $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.booking')]);

        if ($booking != '') {
            Notification::whereJsonContains('data->id', $booking->id)->delete();
            $booking->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.booking')]);
        }
        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bookingAssignForm(Request $request)
    {
        $bookingdata = Booking::with([
            'handymanAdded.handyman', 
            'service', 
            'provider',
            'handymanByAddress.handyman',
            'providerAddress'
        ])->find($request->id);
        
        if (!$bookingdata) {
            return response()->json(['status' => false, 'message' => 'Booking not found'], 404);
        }
        
        $pageTitle = __('messages.assign_form_title', ['form' => __('messages.booking')]);
        return view('booking.assigned_form', compact('bookingdata', 'pageTitle'));
    }

public function bookingAssigned(Request $request)
{
    $bookingdata = Booking::find($request->id);

    $assigned_handyman_ids = [];
    if ($bookingdata->handymanAdded()->count() > 0) {
        $assigned_handyman_ids = $bookingdata->handymanAdded()->pluck('handyman_id')->toArray();
        $bookingdata->handymanAdded()->delete();
        $message = __('messages.transfer_to_handyman');
        $activity_type = 'transfer_booking';
    } else {
        $message = __('messages.assigned_to_handyman');
        $activity_type = 'assigned_booking';
    }

    $remove_notification_id = [];

    if ($request->handyman_id != null) {
        foreach ($request->handyman_id as $handyman) {
            $user = User::where('id', $handyman)->with('handymantype')->first();

            $assign_to_handyman = [
                'booking_id'  => $bookingdata->id,
                'handyman_id' => $handyman,
            ];

            $remove_notification_id = removeArrayValue($assigned_handyman_ids, $handyman);
            $bookingdata->handymanAdded()->insert($assign_to_handyman);
        }
    }

    if (!empty($remove_notification_id)) {
        $search = "id" . '":' . $bookingdata->id;

        Notification::whereIn('notifiable_id', $remove_notification_id)
            ->whereJsonContains('data->id', $bookingdata->id)
            ->delete();
    }

    $bookingdata->status = 'accept';

    // Save per-booking commission if provided
    if ($request->filled('handyman_commission')) {
        $bookingdata->handyman_commission = max(1, min(99, (float) $request->handyman_commission));
    }

    $bookingdata->save();

    $activity_data = [
        'activity_type'    => $activity_type,
        'booking_id'       => $bookingdata->id,
        'booking'          => $bookingdata,
        'activity_message' => $message,
    ];

    $this->sendNotification($activity_data);

    $message = __('messages.save_form', ['form' => __('messages.booking')]);

    if ($request->is('api/*')) {
        return comman_message_response($message);
    }

    return response()->json(['status' => true, 'event' => 'callback', 'message' => $message]);
}



    public function action(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $booking_data = Booking::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('messages.booking')]);
        if ($request->type === 'restore') {
            if ($booking_data != '') {
                $booking_data->restore();
                $msg = __('messages.msg_restored', ['name' => __('messages.booking')]);
            }
        }
        if ($request->type === 'forcedelete') {
            $booking_data->forceDelete();
            $msg = __('messages.msg_forcedelete', ['name' => __('messages.booking')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bookingDetails(Request $request, $id)
    {
        $auth_user = authSession();
        $providerdata = User::with([
            'providerBooking' => function ($query) {
                $query->orderBy('updated_at', 'desc')->with('slots');
            }
        ])->where('user_type', 'provider')->where('id', $id)->first();

        $earningData = array();
        foreach ($providerdata->providerBooking as $booking) {

            $booking_id = $booking->id;
            $provider_name = optional($booking->provider)->display_name ?? '-';
            $provider_image = getSingleMedia(optional($booking->provider), 'profile_image', null);
            $provider_contact = optional($booking->provider)->contact_number ?? '-';
            $provider_email = optional($booking->provider)->email ?? '-';
            $country = optional($booking->provider)->country->name ?? '-';
            $city = optional($booking->provider)->city->name ?? '-';
            $amount = $booking->total_amount;
            $payment_status = optional($booking->payment)->payment_status ?? null;
            // Derive start/end from service_slots if booking fields are empty
            $derivedStart = null;
            $derivedEnd = null;
            if ($booking->relationLoaded('slots') && $booking->slots->count() > 0) {
                $derivedStart = $booking->slots
                    ->map(function ($slot) {
                        return trim(($slot->date ?? '') . ' ' . ($slot->start_time ?? ''));
                    })
                    ->filter()
                    ->sort()
                    ->first();
                $derivedEnd = $booking->slots
                    ->map(function ($slot) {
                        return trim(($slot->date ?? '') . ' ' . ($slot->end_time ?? ''));
                    })
                    ->filter()
                    ->sort()
                    ->last();
            }
            $start_at = $booking->start_at ?: $derivedStart;
            $end_at = $booking->end_at ?: $derivedEnd;
            $earningData[] = [
                'provider_id' => $providerdata->id,
                'booking_id' => $booking->id,
                'provider_name' => $provider_name,
                'provider_image' => $provider_image,
                'provider_email' => $provider_email,
                'country' => $country,
                'city' => $city,
                'provider_contact' => $provider_contact,
                'amount' => $amount,
                'payment_status' => $payment_status,
                'start_at' => $start_at,
                'end_at' => $end_at,
            ];


        }

        if ($request->ajax()) {
            return Datatables::of($earningData)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '-';
                    $booking_id = $row['booking_id'];
                    $btn = "<a href=" . route('booking.show', $booking_id) . "><i class='fas fa-eye'></i></a>";
                    return $btn;
                })
                ->editColumn('provider_name', function ($row) {
                    return view('booking.provider', compact('row'));
                })
                ->editColumn('payment_status', function ($row) {
                    $payment_status = $row['payment_status'];

                    if ($payment_status !== null) {
                        $status = '<span class="text-center text-white badge bg-primary">' . str_replace('_', " ", ucfirst($payment_status)) . '</span>';
                    } else {
                        $status = '<span class="badge text-primary bg-primary-subtle">' . __('messages.pending') . '</span>';
                    }
                    return $status;
                })
                ->editColumn('start_at', function ($row) {
                    if (is_array($row)) {
                        $row = (object)$row;
                    }
                    $startAt = isset($row->start_at) ? $row->start_at : null;
                    if ($startAt !== null) {
                        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
                        $date = optional($datetime)->date_format && optional($datetime)->time_format
                            ? date(optional($datetime)->date_format, strtotime($startAt)) . '  ' . date(optional($datetime)->time_format, strtotime($startAt))
                            : $startAt;
                        return $date;
                    }
                    return null;
                })
                ->editColumn('end_at', function ($row) {
                    if (is_array($row)) {
                        $row = (object)$row;
                    }
                    $endAt = isset($row->end_at) ? $row->end_at : null;
                    if ($endAt !== null) {
                        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
                        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
                        $date = optional($datetime)->date_format && optional($datetime)->time_format
                            ? date(optional($datetime)->date_format, strtotime($endAt)) . '  ' . date(optional($datetime)->time_format, strtotime($endAt))
                            : $endAt;
                        return $date;
                    }
                    return null;
                })
                ->editColumn('amount', function ($row) {
                    return $row['amount'] ? getPriceFormat($row['amount']) : '-';
                })
                ->rawColumns(['action', 'payment_status', 'amount', 'check'])
                ->make(true);
        }
        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.bookings');
        return view('booking.details', compact('pageTitle', 'earningData', 'auth_user', 'providerdata'));
    }

    public function bookingstatus(Request $request, $id)
    {
        $tabpage = $request->tabpage;
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $user_data = User::find($user_id);
        $bookingdata = Booking::with(['handymanAdded.handyman', 'payment', 'bookingExtraCharge', 'bookingAddonService', 'slots',  'service.city','service.country','service', 'bookingRating', 'customer', 'provider'])->myBooking()->find($id);

        $is_enable_advance_payment = $bookingdata->service->is_enable_advance_payment;
        $serviceProof = ServiceProof::where('booking_id',$id)->get();
        // "Review by customer" = customer's review of the provider for THIS booking — load by booking_id so both customer and provider see it
        $review_by_customer_for_booking = BookingRating::with('customer')->where('booking_id', $id)->first();
        // Logged-in user's own review (for "Rate Now" / edit when user is the customer)
        $customer_review = BookingRating::with('customer')->where('customer_id',$user_id)->where('service_id',$bookingdata->service_id)->where('booking_id',$id)->first();
        
        // Provider's review of the customer for THIS booking (from customer_ratings) — load by booking_id only so it always displays when present
        $customer_rating = \App\Models\CustomerRating::where('booking_id', $id)
            ->with('provider')
            ->first();
        
        $payment = Payment::where('booking_id', $id)->orderBy('id', 'desc')->first() ?? null;
        $serviceconfig = Setting::getValueByKey('service-configurations', 'service-configurations');
        $advancePaymentPercentage = isset($serviceconfig->advance_paynment_percantage) ? $serviceconfig->advance_paynment_percantage : 0;
        $global_advance_payment = isset($serviceconfig->global_advance_payment) ? $serviceconfig->global_advance_payment : 0;
        $bookingdata->service->is_enable_advance_payment = $bookingdata->service->is_enable_advance_payment == 1 ? $bookingdata->service->is_enable_advance_payment : $global_advance_payment;
        $bookingdata->service->advance_payment_amount = $bookingdata->service->advance_payment_amount > 0 ? $bookingdata->service->advance_payment_amount : $advancePaymentPercentage;

        // Effective advance % (after global/service merge) — used in booking info pricing table
        $advanceservice = $bookingdata->service->advance_payment_amount;

        // Check if customer rating already exists for this booking
        $customer_rating_exists = CustomerRating::where('booking_id', $id)->exists();

        $handyman_ratings_for_booking = HandymanRating::where('booking_id', $id)
            ->with('handyman')
            ->get()
            ->keyBy('handyman_id');

        $can_rate_workers_on_booking = auth()->check()
            && (int) auth()->id() === (int) $bookingdata->customer_id
            && isset($payment)
            && $payment->payment_status === 'paid'
            && in_array($bookingdata->status, ['completed', 'paid'], true);

        switch ($tabpage) {
            case 'info':
                $data = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'bookingdata', 'payment', 'advanceservice', 'customer_review', 'review_by_customer_for_booking', 'customer_rating', 'serviceProof', 'is_enable_advance_payment', 'customer_rating_exists', 'handyman_ratings_for_booking', 'can_rate_workers_on_booking'))->render();
                break;
            case 'status':
                $data = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'bookingdata', 'payment'))->render();
                break;
            default:
                $data = view('booking.' . $tabpage, compact('tabpage', 'auth_user', 'bookingdata'))->render();
                break;
        }
        return response()->json($data);
    }

    /**
     * Same rules as LanguageTranslator (web): domain → session (if switcher) → app.locale.
     * Does not use user language_option so persotel.de stays de even when profile says en.
     */
    protected function resolveInvoicePdfLocale(): string
    {
        $domainLocale = config('app.domain_locale', []);
        $host = request()->getHost();
        $hostVariants = array_unique(array_filter([
            $host,
            preg_replace('/^www\./i', '', $host),
            str_starts_with(strtolower($host), 'www.') ? null : 'www.'.$host,
        ]));
        foreach ($hostVariants as $h) {
            if ($h !== '' && isset($domainLocale[$h])) {
                return $domainLocale[$h];
            }
        }
        if (config('app.show_language_switcher', false) && session()->has('locale')) {
            return session('locale');
        }

        return config('app.locale', 'en');
    }

    public function createPDF($id)
    {
        $previousLocale = app()->getLocale();
        $previousCarbonLocale = Carbon::getLocale();
        $locale = request('lang') ?: $this->resolveInvoicePdfLocale();
        App::setLocale($locale);
        Carbon::setLocale($locale);

        try {
            $data = AppSetting::take(1)->first();
            $bookingdata = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge', 'bookingPackage', 'bookingAddonService')->myBooking()->find($id);
            if (empty($bookingdata)) {
                abort(404);
            }
            $payment = Payment::where('booking_id', $id)->orderBy('id', 'desc')->first() ?? null;
            $serviceconfig = Setting::getValueByKey('service-configurations', 'service-configurations');
            $advancePaymentPercentage = isset($serviceconfig->advance_paynment_percantage) ? $serviceconfig->advance_paynment_percantage : 0;
            $global_advance_payment = isset($serviceconfig->global_advance_payment) ? $serviceconfig->global_advance_payment : 0;
            $bookingdata->service->is_enable_advance_payment = $bookingdata->service->is_enable_advance_payment == 1 ? $bookingdata->service->is_enable_advance_payment : $global_advance_payment;
            $bookingdata->service->advance_payment_amount = $bookingdata->service->advance_payment_amount > 0 ? $bookingdata->service->advance_payment_amount : $advancePaymentPercentage;
            $pdf = Pdf::loadView('booking.invoice', ['bookingdata' => $bookingdata, 'data' => $data, 'payment' => $payment]);

            return $pdf->stream('invoice_'.$bookingdata->id.'.pdf');
        } finally {
            App::setLocale($previousLocale);
            Carbon::setLocale($previousCarbonLocale);
        }
    }

    public function updateStatus(Request $request)
    {
        $oldStatus = null;
        $bookingdata = null;
        
        switch ($request->type) {
            case 'payment':
                $payment = Payment::where('booking_id', $request->bookingId)->first();
                if ($payment) {
                    $oldStatus = $payment->payment_status;
                    $payment->update(['payment_status' => $request->status]);
                }
                break;
            default:
                $bookingdata = Booking::find($request->bookingId);
                if ($bookingdata) {
                    $oldStatus = $bookingdata->status;
                    $bookingdata->update(['status' => $request->status]);
                    
                    // Run notification/email job synchronously (no queue worker required)
                    // Only run if status actually changed to avoid unnecessary work
                    if ($oldStatus != $request->status) {
                        $actorId = auth()->id();
                        $mailLocale = app()->getLocale();
                        \App\Jobs\ProcessBookingStatusUpdateJob::dispatchSync(
                            $bookingdata->id,
                            $request->all(),
                            $oldStatus,
                            $actorId,
                            $mailLocale
                        );
                    }
                }
                break;
        }

        return comman_custom_response(['message' => 'Status Updated', 'status' => true]);
    }

    public function saveBookingRating(Request $request)
    {
        $rating_data = $request->all();
        $result = BookingRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('messages.update_form', ['form' => __('messages.rating')]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.rating')]);
        }

        return redirect()->back()->withSuccess($message);
    }

    public function getPaymentMethod(Request $request)
    {
        $data = $request->all();
        $data['datetime'] = now();

        $data['payment_status'] = 'failed';

        $payment_data = Payment::where('booking_id', $data['booking_id'])->first();

//        if (!empty($payment_data)) {
//            $payment_data->update($data);
//        } else {
            $payment_data = Payment::create($data);
//        }
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
        // Derive currency code dynamically from default_currency (Country)
        try {
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? \App\Models\Country::find($countryId) : null;
            $currencyCode = strtoupper((string)($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }
        $data['currency_code'] = $currencyCode;

        switch ($data['payment_type']) {
            case 'stripe':
                $data['payment_geteway_data'] = getPaymentMethodkey($data['payment_type']);
                break;
            case 'paypal':
                $data['payment_geteway_data'] = getPaymentMethodkey($data['payment_type']);
                break;

            default:

                break;
        }

        return comman_custom_response($data);
    }


    public function createStripePayment(Request $request)
    {
        $data = $request->all();

        // Flutter / API in-app: use PaymentIntent (no WebView/redirect). Use when: platform=flutter, use_payment_intent=1, or API expects JSON
        $usePaymentIntent = $request->boolean('use_payment_intent')
            || (isset($data['platform']) && in_array(strtolower((string) $data['platform']), ['flutter', 'mobile', 'app'], true))
            || $request->expectsJson();

        if ($usePaymentIntent) {
            return $this->createBookingStripePaymentIntent($request, $data);
        }

        $checkout_session = getstripepayments($data);
        if (isset($checkout_session['message'])) {
            return comman_custom_response($checkout_session);
        }

        Payment::where('booking_id', $data['booking_id'])->update(['other_transaction_detail' => $checkout_session['id']]);
        return comman_custom_response($checkout_session);
    }

    /**
     * Create Stripe PaymentIntent for in-app (Flutter) payment. Returns client_secret for Stripe SDK.
     */
    protected function createBookingStripePaymentIntent(Request $request, array $data)
    {
        $booking = Booking::where('id', $data['booking_id'])->with('service')->first();
        if (!$booking) {
            return response()->json(['status' => false, 'message' => __('messages.booking_not_found')], 404);
        }

        $stripe_key_data = getPaymentMethodkey($data['payment_type'] ?? 'stripe');
        $stripe_secret = $stripe_key_data['stripe_key'] ?? null;
        if (!$stripe_secret) {
            return response()->json(['status' => false, 'message' => 'Stripe not configured'], 500);
        }

        try {
            $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
            $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;
            $countryId = $sitesetupdata['default_currency'] ?? null;
            $country = $countryId ? Country::find($countryId) : null;
            $currencyCode = strtoupper((string) ($country->currency_code ?? 'EUR'));
        } catch (\Throwable $e) {
            $currencyCode = 'EUR';
        }

        $type = $data['type'] ?? 'advance_payment';
        if (($type ?? '') === 'full_payment') {
            $total_amount = $booking->total_amount - ($booking->advance_paid_amount ?? 0);
        } else {
            $total_amount = (float) ($data['total_amount'] ?? $booking->advance_paid_amount ?? $booking->total_amount);
        }

        $stripe = new \Stripe\StripeClient($stripe_secret);
        $intent = $stripe->paymentIntents->create([
            'amount' => stripe_unit_amount_from_decimal($total_amount, $currencyCode),
            'currency' => strtolower($currencyCode),
            'description' => 'Booking #' . $booking->id . ' - ' . ($type === 'advance_payment' ? 'Advance' : 'Remaining') . ' Payment',
            'payment_method_types' => ['card'],
            'metadata' => [
                'booking_id' => (string) $booking->id,
                'type' => $type,
                'customer_id' => (string) ($data['customer_id'] ?? $booking->customer_id),
                'provider_id' => (string) $booking->provider_id,
            ],
        ]);

        Payment::where('booking_id', $data['booking_id'])->update([
            'other_transaction_detail' => $intent->id,
        ]);

        return response()->json([
            'status' => true,
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'booking_id' => (int) $data['booking_id'],
            'type' => $type,
        ]);
    }

public function saveStripePayment(Request $request, $id)
{
    $type = $request->type;
    $result = Payment::where('booking_id', $id)->latest()->first();

    $stripe_session_id = $result->other_transaction_detail;
    $payment_type = $result->payment_type;

    $session_object = getstripePaymnetId($stripe_session_id, $payment_type);

    if ($session_object['payment_intent'] !== '' && $session_object['payment_status'] == 'paid') {
        $result->txn_id = $session_object['payment_intent'];

        if ($type == 'advance_payment') {
            $result->payment_status = 'advanced_paid';
        } else {
            $result->payment_status = 'paid';
        }
    }

    $booking = Booking::find($id);
    $admin_user_id = User::where('user_type', 'admin')->value('id');
    $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;

    if (!empty($result) && $result->payment_status == 'advanced_paid') {
        $booking->advance_paid_amount = $result->total_amount;
//        $booking->status = 'pending';

        $advance_paid_amount = $result->total_amount;
        $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;

        // BUG-3: idempotency — skip if admin commission already credited for this booking+type
        $alreadyCredited = CommissionEarning::where('booking_id', $booking->id)
            ->where('user_type', 'admin')
            ->where('commission_status', 'paid')
            ->whereNull('post_job_bid_request_id')
            ->exists();

        if (!$alreadyCredited) {
            // Hold provider advance payout; credit admin only
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid',
            ]);
        }
    }

    if (!empty($result) && $result->payment_status == 'paid') {
        $booking->status = 'completed';
        $booking->update();

        $advance_paid = $booking->advance_paid_amount ?? 0;
        $total_amount = $booking->total_amount;
        $remaining_amount = $total_amount - $advance_paid;
        $result->total_amount = $remaining_amount;
        $result->save();

        // BUG-3: idempotency — skip commission crediting if provider was already paid
        $remainingAlreadyCredited = CommissionEarning::where('booking_id', $booking->id)
            ->where('user_type', 'provider')
            ->where('commission_status', 'paid')
            ->whereNull('post_job_bid_request_id')
            ->exists();

        if (!$remainingAlreadyCredited) {
        // Admin: 10% on remaining amount only (not on extra charges); once per remaining payment
        $extra_total = $booking->getExtraChargeValue();
        $remaining_admin_commission = ($remaining_amount > 0)
            ? ($remaining_amount * $admin_commission_percentage) / 100
            : 0;

        // Pool = 90% of advance (held) + (90% of remaining - extra charges). Provider gets 90% of extra charges.
        $provider_side_advance = ($advance_paid * (100 - $admin_commission_percentage)) / 100;
        // Provider + handymen get 90% of remaining amount
        $provider_side_remaining = ($remaining_amount * 90) / 100;
        $pool = $provider_side_advance + max(0, $provider_side_remaining - $extra_total);

        // From pool: handyman gets his commission % of pool; rest + extra_total goes to provider
        $handymen = BookingHandymanMapping::where('booking_id', $booking->id)->pluck('handyman_id');
        $handyman_payouts = [];
        $total_handyman_share = 0;
        foreach ($handymen as $handyman_id) {
            $handyman = User::find($handyman_id);
            if (!$handyman) {
                continue;
            }
            // Use per-booking commission if set; fall back to handyman's default
            $bookingCommission = $booking->handyman_commission;
            if ($bookingCommission !== null && $bookingCommission > 0) {
                $commission_percent = max(1, min(99, $bookingCommission));
            } elseif ($handyman->handyman_commission !== null) {
                $commission_percent = max(1, min(99, $handyman->handyman_commission));
            } else {
                continue;
            }
            $handyman_share = ($pool * $commission_percent) / 100;
            $total_handyman_share += $handyman_share;
            $handyman_payouts[] = [
                'handyman_id' => $handyman_id,
                'amount' => $handyman_share,
            ];
        }

        $provider_from_pool = $pool - $total_handyman_share;
        if ($provider_from_pool < 0) {
            $provider_from_pool = 0;
        }
        // Extra charges 100% to provider (no admin commission on extra)
        $provider_extra_earning = $extra_total;
        $provider_final_earning = $provider_from_pool + $provider_extra_earning;

        // Pay handymen
        foreach ($handyman_payouts as $payout) {
            Wallet::firstOrCreate(['user_id' => $payout['handyman_id']])->increment('amount', $payout['amount']);

            HandymanPayout::create([
                'handyman_id' => $payout['handyman_id'],
                'booking_id' => $booking->id,
                'amount' => $payout['amount'],
                'status' => 'paid',
                'payment_id' => $result->id ?? null,
                'paid_date' => Carbon::now(),
                'payment_method' => 'wallet',
                'payment_gateway' => 'wallet',
            ]);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'handyman',
                'employee_id' => $payout['handyman_id'],
                'commission_amount' => $payout['amount'],
                'commission_status' => 'paid',
            ]);
        }

        if ($remaining_admin_commission > 0) {
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $remaining_admin_commission);

            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $remaining_admin_commission,
                'commission_status' => 'paid',
            ]);
        }

        Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_final_earning);

        ProviderPayout::create([
            'provider_id' => $booking->provider_id,
            'amount' => $provider_final_earning,
            'payment_id' => $result->id ?? null,
            'payment_method' => 'stripe',
            'paid_date' => Carbon::now(),
            'status' => 'paid',
            'booking_id' => $booking->id,
            'payment_gateway' => 'stripe',
        ]);

        CommissionEarning::create([
            'booking_id' => $booking->id,
            'user_type' => 'provider',
            'employee_id' => $booking->provider_id,
            'commission_amount' => $provider_final_earning,
            'commission_status' => 'paid',
        ]);

        CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
        } // end !$remainingAlreadyCredited
    }

    // ✅ Always create a new PaymentHistory entry
    $firstHandymanId = optional($booking->handymanAdded->first())->handyman_id;
    $assignedUserData = User::find($firstHandymanId);

    if ($firstHandymanId && $assignedUserData->user_type == 'provider') {
        $payment_history = [
            'payment_id' => $result->id,
            'booking_id' => $result->booking_id,
            'parent_id' => $result->booking_id, // temporary, will update below
            'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
            'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
            'sender_id' => $booking->customer_id,
            'receiver_id' => $firstHandymanId,
            'datetime' => now(),
            'total_amount' => $request->total_amount,
            'txn_id' => $result->txn_id,
            'type' => $result->payment_type,
            'text' => __('messages.payment_transfer', [
            'from' => get_user_name($request->customer_id),
            'to' => get_user_name($firstHandymanId),
            'amount' => getPriceFormat(
                ($result->payment_status == 'paid')
                    ? ($booking->total_amount - ($booking->advance_paid_amount ?? 0))
                    : (float)$request->total_amount
            ),
        ]),

        ];

        $res = PaymentHistory::create($payment_history);
        $res->parent_id = $res->id;
        $res->save();
    }
    $result->update();

    $booking->payment_id = $result->id;
    $booking->update();

    $this->sendNotification([
        'activity_type' => 'payment_message_status',
        'payment_status' => str_replace("_", " ", ucfirst($result->payment_status)),
        'booking_id' => $booking->id,
        'booking' => $booking,
    ]);

    // API / Flutter: return JSON instead of redirect so app can show success and close WebView
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
            'status'  => true,
            'message' => __('messages.payment_completed'),
            'booking_id' => (int) $id,
        ]);
    }

    return redirect('/booking-list');
}

    /**
     * Confirm Stripe payment after in-app (Flutter) PaymentIntent success. Call this after Stripe SDK confirms payment.
     * POST body: booking_id, payment_intent_id, type (advance_payment|full_payment)
     */
    public function confirmStripePaymentIntent(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'payment_intent_id' => 'required|string',
            'type' => 'required|in:advance_payment,full_payment',
        ]);

        $bookingId = (int) $request->booking_id;
        $intentId = (string) $request->payment_intent_id;
        $type = $request->type;

        $result = Payment::where('booking_id', $bookingId)->latest()->first();
        if (!$result || (string) $result->other_transaction_detail !== $intentId) {
            return response()->json(['status' => false, 'message' => 'Payment record not found or intent mismatch'], 422);
        }

        $stripe_key_data = getPaymentMethodkey($result->payment_type ?? 'stripe');
        $stripe_secret = $stripe_key_data['stripe_key'] ?? null;
        if (!$stripe_secret) {
            return response()->json(['status' => false, 'message' => 'Stripe not configured'], 500);
        }

        $stripe = new \Stripe\StripeClient($stripe_secret);
        try {
            $intent = $stripe->paymentIntents->retrieve($intentId, []);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Stripe verification failed: ' . $e->getMessage()], 422);
        }

        if (($intent->status ?? '') !== 'succeeded') {
            return response()->json(['status' => false, 'message' => 'Payment not completed', 'intent_status' => $intent->status ?? null], 422);
        }

        $payAmount = (float) (($intent->amount_received ?? $intent->amount ?? 0) / 100.0);
        $result->txn_id = $intent->id;
        $result->payment_status = $type === 'advance_payment' ? 'advanced_paid' : 'paid';
        $result->total_amount = $payAmount;
        $result->save();

        $booking = Booking::find($bookingId);
        $id = $bookingId;
        $admin_user_id = User::where('user_type', 'admin')->value('id');
        $admin_commission_percentage = Setting::getValueByKey('admin_commission_percentage', 'site-setup')->value ?? 10;

        if (!empty($result) && $result->payment_status == 'advanced_paid') {
            $booking->advance_paid_amount = $result->total_amount;
            $advance_paid_amount = $result->total_amount;
            $admin_commission_amount = ($advance_paid_amount * $admin_commission_percentage) / 100;
            Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $admin_commission_amount);
            CommissionEarning::create([
                'booking_id' => $booking->id,
                'user_type' => 'admin',
                'employee_id' => $admin_user_id,
                'commission_amount' => $admin_commission_amount,
                'commission_status' => 'paid',
            ]);
        }

        if (!empty($result) && $result->payment_status == 'paid') {
            $booking->status = 'completed';
            $booking->update();
            $advance_paid = $booking->advance_paid_amount ?? 0;
            $total_amount = $booking->total_amount;
            $remaining_amount = $total_amount - $advance_paid;
            $result->total_amount = $remaining_amount;
            $result->save();
            $extra_total = $booking->getExtraChargeValue();

            // Idempotency: skip payout creation if provider commission already credited
            $providerAlreadyPaid = CommissionEarning::where('booking_id', $booking->id)
                ->where('user_type', 'provider')
                ->where('commission_status', 'paid')
                ->whereNull('post_job_bid_request_id')
                ->exists();

            if (!$providerAlreadyPaid) {
                // Admin: 10% on remaining amount only (not on extra charges); once per remaining payment
                $remaining_admin_commission = ($remaining_amount > 0)
                    ? ($remaining_amount * $admin_commission_percentage) / 100
                    : 0;

                $provider_side_advance = ($advance_paid * (100 - $admin_commission_percentage)) / 100;
                // Provider + handymen get 90% of remaining amount
                $provider_side_remaining = ($remaining_amount * 90) / 100;
                $pool = $provider_side_advance + max(0, $provider_side_remaining - $extra_total);
                $handymen = BookingHandymanMapping::where('booking_id', $booking->id)->pluck('handyman_id');
                $handyman_payouts = [];
                $total_handyman_share = 0;
                foreach ($handymen as $handyman_id) {
                    $handyman = User::find($handyman_id);
                    if (!$handyman) continue;
                    $bookingCommission = $booking->handyman_commission;
                    if ($bookingCommission !== null && $bookingCommission > 0) {
                        $commission_percent = max(1, min(99, $bookingCommission));
                    } elseif ($handyman->handyman_commission !== null) {
                        $commission_percent = max(1, min(99, $handyman->handyman_commission));
                    } else {
                        continue;
                    }
                    $handyman_share = ($pool * $commission_percent) / 100;
                    $total_handyman_share += $handyman_share;
                    $handyman_payouts[] = ['handyman_id' => $handyman_id, 'amount' => $handyman_share];
                }
                $provider_from_pool = $pool - $total_handyman_share;
                if ($provider_from_pool < 0) $provider_from_pool = 0;
                $provider_extra_earning = $extra_total;
                $provider_final_earning = $provider_from_pool + $provider_extra_earning;
                foreach ($handyman_payouts as $payout) {
                    Wallet::firstOrCreate(['user_id' => $payout['handyman_id']])->increment('amount', $payout['amount']);
                    HandymanPayout::create([
                        'handyman_id' => $payout['handyman_id'],
                        'booking_id' => $booking->id,
                        'payment_id' => $result->id ?? null,
                        'amount' => $payout['amount'],
                        'status' => 'paid',
                        'paid_date' => Carbon::now(),
                        'payment_method' => 'stripe',
                        'payment_gateway' => 'stripe',
                    ]);
                    CommissionEarning::create([
                        'booking_id' => $booking->id,
                        'user_type' => 'handyman',
                        'employee_id' => $payout['handyman_id'],
                        'commission_amount' => $payout['amount'],
                        'commission_status' => 'paid',
                    ]);
                }
                if ($remaining_admin_commission > 0) {
                    Wallet::firstOrCreate(['user_id' => $admin_user_id])->increment('amount', $remaining_admin_commission);
                    CommissionEarning::create([
                        'booking_id' => $booking->id,
                        'user_type' => 'admin',
                        'employee_id' => $admin_user_id,
                        'commission_amount' => $remaining_admin_commission,
                        'commission_status' => 'paid',
                    ]);
                }
                Wallet::firstOrCreate(['user_id' => $booking->provider_id])->increment('amount', $provider_final_earning);
                ProviderPayout::create([
                    'provider_id' => $booking->provider_id,
                    'payment_id' => $result->id ?? null,
                    'amount' => $provider_final_earning,
                    'payment_method' => 'stripe',
                    'paid_date' => Carbon::now(),
                    'status' => 'paid',
                    'booking_id' => $booking->id,
                    'payment_gateway' => 'stripe',
                ]);
                CommissionEarning::create([
                    'booking_id' => $booking->id,
                    'user_type' => 'provider',
                    'employee_id' => $booking->provider_id,
                    'commission_amount' => $provider_final_earning,
                    'commission_status' => 'paid',
                ]);
                CommissionEarning::where('booking_id', $booking->id)->update(['commission_status' => 'paid']);
            }
        }

        $firstHandymanId = optional($booking->handymanAdded->first())->handyman_id;
        $assignedUserData = User::find($firstHandymanId);
        if ($firstHandymanId && $assignedUserData && $assignedUserData->user_type == 'provider') {
            $payment_history = [
                'payment_id' => $result->id,
                'booking_id' => $result->booking_id,
                'parent_id' => $result->booking_id,
                'action' => config('constant.PAYMENT_HISTORY_ACTION.CUSTOMER_SEND_PROVIDER'),
                'status' => config('constant.PAYMENT_HISTORY_STATUS.PENDING_PROVIDER'),
                'sender_id' => $booking->customer_id,
                'receiver_id' => $firstHandymanId,
                'datetime' => now(),
                'total_amount' => $payAmount,
                'txn_id' => $result->txn_id,
                'type' => $result->payment_type,
                'text' => __('messages.payment_transfer', [
                    'from' => get_user_name($booking->customer_id),
                    'to' => get_user_name($firstHandymanId),
                    'amount' => getPriceFormat($result->payment_status == 'paid' ? ($booking->total_amount - ($booking->advance_paid_amount ?? 0)) : (float) $payAmount),
                ]),
            ];
            $res = PaymentHistory::create($payment_history);
            $res->parent_id = $res->id;
            $res->save();
        }
        $result->update();
        $booking->payment_id = $result->id;
        $booking->update();
        $this->sendNotification([
            'activity_type' => 'payment_message_status',
            'payment_status' => str_replace('_', ' ', ucfirst($result->payment_status)),
            'booking_id' => $booking->id,
            'booking' => $booking,
        ]);

        return response()->json([
            'status' => true,
            'message' => __('messages.payment_completed'),
            'booking_id' => $bookingId,
        ]);
    }

    public function getEarningsBreakdown(Request $request)
    {
        $authRole = auth()->user()->roles->pluck('name')->first();
        $bookings = Booking::query()->where('status', '!=', 'cancelled')->with('commissionsdata', 'payment', 'handymanAdded');

        // Apply filters from the request
        if ($request->has('advanceFilter')) {
            $advanceFilter = $request->advanceFilter;

            // Regular filters
            $filters = [
                'customer_id' => 'customer_id',
                'service_id' => 'service_id',
                'provider_id' => 'provider_id',
                'handyman_id' => ['handymanAdded', 'handyman_id'],
                'booking_status' => 'status',
                'payment_status' => ['payment', 'payment_status'],
                'payment_type' => ['payment', 'payment_type'],
                'date_range' => null, // Special handling for date range
            ];

            foreach ($filters as $key => $filter) {
                if (!empty($advanceFilter[$key])) {
                    if ($key === 'date_range') {
                        $dates = explode(' to ', $advanceFilter['date_range']);
                        if (count($dates) === 2) {
                            $bookings->whereDate('date', '>=', $dates[0])
                                ->whereDate('date', '<=', $dates[1]);
                        } elseif (count($dates) === 1) {
                            $bookings->whereDate('date', $dates[0]);
                        }
                    } else {
                        if (is_array($advanceFilter[$key])) {
                            if (is_array($filter)) {
                                $bookings->whereHas($filter[0], function ($query) use ($filter, $advanceFilter, $key) {
                                    $query->whereIn($filter[1], $advanceFilter[$key]);
                                });
                            } else {
                                $bookings->whereIn($filter, $advanceFilter[$key]);
                            }
                        } else {
                            $bookings->where($filter, $advanceFilter[$key]);
                        }
                    }
                }
            }
        }

        // Apply role-based filtering
        switch ($authRole) {
            case 'admin':
            case 'demo_admin':
                $bookings = $bookings->get();
                break;

            case 'provider':
                $bookings = $bookings->where('provider_id', auth()->user()->id)->get();
                break;

            case 'handyman':
                $bookings = $bookings->whereHas('handymanAdded', function ($query) {
                    $query->where('handyman_id', auth()->user()->id);
                })->get();
                break;

            default:
                $bookings = collect();
                break;
        }

        // Initialize earnings array
        $earnings = [
            'admin' => 0,
            'provider' => 0,
            'handyman' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'totalAmountWithDiscount' => 0,
            'totalAmountWithoutDiscount' => 0,
        ];

        foreach ($bookings as $booking) {
            // Get the raw total amount before any adjustments
            $rawTotal = $booking->total_amount + ($booking->final_discount_amount ?? 0);

            // Calculate actual total after discount
            $actualTotal = $booking->total_amount;

            // Handle commission distribution
            if ($booking->handymanAdded->count() > 0) {
                foreach ($booking->commissionsdata as $commission) {
                    switch ($commission->user_type) {
                        case 'admin':
                            $earnings['admin'] += $commission->commission_amount;
                            break;
                        case 'provider':
                            $earnings['provider'] += $commission->commission_amount;
                            break;
                        case 'handyman':
                            $earnings['handyman'] += $commission->commission_amount;
                            break;
                    }
                }
                // Track components
                $earnings['tax'] += $booking->final_total_tax ?? 0;
                $earnings['discount'] += $booking->final_discount_amount ?? 0;

                // Update totals - switched rawTotal and actualTotal
                $earnings['totalAmountWithoutDiscount'] += $actualTotal;
                $earnings['totalAmountWithDiscount'] += $rawTotal;
                $earnings['total'] += $rawTotal; // Changed to rawTotal to show amount before discount

            }
            // else {
            //     // If no handyman, provider gets the full amount after discount and tax
            //     // If no handyman, provider gets the full amount after discount and tax
            //     $earnings['provider'] += ($actualTotal - ($booking->final_total_tax ?? 0));
            // }


        }

        // Round all values to 2 decimal places for consistency
        foreach ($earnings as $key => $value) {
            $earnings[$key] = round($value, 2);
        }

        return response()->json([
            'totalEarning' => number_format($earnings['total'], 2),
            'earnings' => $earnings,
            'userRole' => $authRole,
        ]);
    }

    /**
     * Create direct database notification as fallback
     * This ensures notifications are saved even if queue fails or templates are missing
     */
    private function createDirectDatabaseNotification($booking, $activity_type, $old_status, $new_status)
    {
        try {
            $statusLabel = ucwords(str_replace('_', ' ', $new_status));
            $oldStatusLabel = ucwords(str_replace('_', ' ', $old_status));
            $serviceName = optional($booking->service)->name ?? 'Service';
            $customerName = optional($booking->customer)->display_name ?? optional($booking->customer)->name ?? 'Customer';
            $providerName = optional($booking->provider)->display_name ?? optional($booking->provider)->name ?? 'Provider';
            
            // Create a proper notification message
            $message = "Booking #{$booking->id} for {$serviceName} status has been updated from {$oldStatusLabel} to {$statusLabel}";
            
            $notificationData = [
                'id' => $booking->id,
                'type' => $activity_type,
                'subject' => 'Booking Status Updated',
                'booking_id' => $booking->id,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'status_label' => $statusLabel,
                'service_name' => $serviceName,
                'customer_name' => $customerName,
                'provider_name' => $providerName,
                'message' => $message,
                'notification-type' => 'booking',
                'created_at' => now()->toDateTimeString(),
            ];

            // Notify customer
            if ($booking->customer_id) {
                \DB::table('notifications')->insert([
                    'id' => \Str::uuid()->toString(),
                    'type' => 'App\Notifications\CommonNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $booking->customer_id,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notify provider
            if ($booking->provider_id) {
                \DB::table('notifications')->insert([
                    'id' => \Str::uuid()->toString(),
                    'type' => 'App\Notifications\CommonNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $booking->provider_id,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notify handymen if any
            if ($booking->handymanAdded && $booking->handymanAdded->count() > 0) {
                foreach ($booking->handymanAdded as $handymanMapping) {
                    if ($handymanMapping->handyman_id) {
                        \DB::table('notifications')->insert([
                            'id' => \Str::uuid()->toString(),
                            'type' => 'App\Notifications\CommonNotification',
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $handymanMapping->handyman_id,
                            'data' => json_encode($notificationData),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create direct database notification (web): ' . $e->getMessage());
        }
    }

}
