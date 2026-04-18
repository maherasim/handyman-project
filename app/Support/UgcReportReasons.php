<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Human-readable labels for UGC report reason codes (same vocabulary as UgcSafetyController).
 * Source of truth for copy: messages.profile_report_reasons in resources/lang.
 */
class UgcReportReasons
{
    public static function label(?string $reason): string
    {
        if ($reason === null || $reason === '') {
            return '—';
        }

        $labels = Lang::get('messages.profile_report_reasons', [], app()->getLocale());
        if (is_array($labels) && isset($labels[$reason])) {
            return $labels[$reason];
        }

        return Str::headline(str_replace('_', ' ', $reason));
    }
}
