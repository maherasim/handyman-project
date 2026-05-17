<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\CommissionEarning;
use App\Models\BookingHandymanMapping;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Traits\NotificationTrait;

class ProcessBookingStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    protected $bookingId;
    protected $data; // Contains 'status' and optionally 'type'
    protected $oldStatus;
    protected $actorId;
    protected $mailLocale;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bookingId, $data, $oldStatus, $actorId, $mailLocale = null)
    {
        $this->bookingId = $bookingId;
        $this->data = $data; // e.g. ['status' => 'accepted']
        $this->oldStatus = $oldStatus;
        $this->actorId = $actorId;
        $this->mailLocale = $mailLocale ?: app()->getLocale();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Re-fetch booking with relationships
        $bookingdata = Booking::with(['customer', 'provider', 'service', 'handymanAdded.handyman', 'payment'])
            ->find($this->bookingId);

        if (!$bookingdata) {
            Log::error("ProcessBookingStatusUpdateJob: Booking not found ID: {$this->bookingId}");
            return;
        }

        // Hydrate necessary data for NotificationTrait
        $bookingdata->old_status = $this->oldStatus;
        $activity_type = 'update_booking_status';

        $activity_data = [
            'activity_type' => $activity_type,
            'booking_id' => $this->bookingId,
            'booking' => $bookingdata,
        ];

        try {
            // 1. Send System Notifications (Traits/NotificationTrait)
            $this->sendNotification($activity_data);

            // 2. Direct Database Notification fallback
            $this->createDirectDatabaseNotification($bookingdata, $activity_type, $this->oldStatus, $this->data['status']);

        } catch (\Exception $e) {
            Log::error('ProcessBookingStatusUpdateJob: Failed to send notification: ' . $e->getMessage());
        }

        // 3. Send Emails (Explicit Logic moved from Controller)
        // Only if status actually changed (Controller check passed, but double check good practice)
        if ($this->oldStatus != $this->data['status']) {
            try {
                $this->sendBookingStatusEmails($bookingdata, $this->oldStatus, $this->data['status']);
            } catch (\Exception $e) {
                Log::error('ProcessBookingStatusUpdateJob: Failed to send emails: ' . $e->getMessage());
            }
        }
    }

    /**
     * Encapsulated email sending logic from BookingController
     */
    protected function sendBookingStatusEmails($bookingdata, $oldStatus, $newStatus)
    {
        $actor = User::find($this->actorId);
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

        $emailsToSend = [];

        if ($actorType === 'handyman') {
            // Handyman action: notify provider and user
            if ($bookingdata->provider && $bookingdata->provider->email) {
                $emailsToSend[] = ['user' => $bookingdata->provider, 'type' => 'provider'];
            }
            if ($bookingdata->customer && $bookingdata->customer->email) {
                $emailsToSend[] = ['user' => $bookingdata->customer, 'type' => 'user'];
            }
        } elseif ($actorType === 'provider') {
            // Provider action: notify user and handyman
            if ($bookingdata->customer && $bookingdata->customer->email) {
                $emailsToSend[] = ['user' => $bookingdata->customer, 'type' => 'user'];
            }
            if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                foreach ($bookingdata->handymanAdded as $handymanMapping) {
                    if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                        $emailsToSend[] = ['user' => $handymanMapping->handyman, 'type' => 'handyman'];
                    }
                }
            }
        } elseif ($actorType === 'user') {
            // User action: notify provider and handyman
            if ($bookingdata->provider && $bookingdata->provider->email) {
                $emailsToSend[] = ['user' => $bookingdata->provider, 'type' => 'provider'];
            }
            if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                foreach ($bookingdata->handymanAdded as $handymanMapping) {
                    if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                        $emailsToSend[] = ['user' => $handymanMapping->handyman, 'type' => 'handyman'];
                    }
                }
            }
        } else {
            // System/admin action: notify all parties
            if ($bookingdata->provider && $bookingdata->provider->email) {
                $emailsToSend[] = ['user' => $bookingdata->provider, 'type' => 'provider'];
            }
            if ($bookingdata->customer && $bookingdata->customer->email) {
                $emailsToSend[] = ['user' => $bookingdata->customer, 'type' => 'user'];
            }
            if ($bookingdata->handymanAdded && $bookingdata->handymanAdded->count() > 0) {
                foreach ($bookingdata->handymanAdded as $handymanMapping) {
                    if ($handymanMapping->handyman && $handymanMapping->handyman->email) {
                        $emailsToSend[] = ['user' => $handymanMapping->handyman, 'type' => 'handyman'];
                    }
                }
            }
        }

        foreach ($emailsToSend as $emailData) {
            try {
                Mail::to($emailData['user']->email)->locale($this->mailLocale)->send(
                    new \App\Mail\BookingStatusUpdateMail(
                        $emailData['user'],
                        $bookingdata,
                        $oldStatus,
                        $newStatus,
                        $actorName,
                        $actorType,
                        $emailData['type'], // recipient type: 'provider', 'handyman', or 'user'
                        $this->mailLocale
                    )
                );
            } catch (\Exception $e) {
                Log::error('Failed to send booking status update email (Job): ' . $e->getMessage());
            }
        }
    }

    // Helper duplicated from Controller
    protected function createDirectDatabaseNotification($booking, $activity_type, $old_status, $new_status)
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
            Log::error('ProcessBookingStatusUpdateJob: Failed to create direct database notification: ' . $e->getMessage());
        }
    }
}
