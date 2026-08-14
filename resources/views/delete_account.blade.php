<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delete Account – {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backend.css') }}">
    <style>
        body { background: #f4f6fb; font-family: 'Nunito', sans-serif; }
        .card { border-radius: 16px; }
        .btn-danger { background: #dc3545; border: none; }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card shadow-sm border-0 p-4" style="max-width:480px;width:100%;">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Delete Account Request</h4>
                <p class="text-muted small">Submit your request below. We will process it within 7 business days and permanently delete your account and all associated data.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle me-2"></i>
                    Your deletion request has been submitted. You will receive a confirmation within 7 business days.
                </div>
            @else
                <form method="POST" action="{{ route('delete-account.submit') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Enter your registered email"
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Reason for Deletion <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="4"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Please tell us why you want to delete your account"
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning small mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Account deletion is permanent and cannot be undone. All your data including bookings, history, and profile will be permanently removed.
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                        <i class="fas fa-trash me-2"></i> Submit Deletion Request
                    </button>
                </form>
            @endif

            <p class="text-center text-muted small mt-4 mb-0">
                Need help? Contact us at <a href="mailto:info@frobster.com">info@frobster.com</a>
            </p>
        </div>
    </div>
</body>
</html>
