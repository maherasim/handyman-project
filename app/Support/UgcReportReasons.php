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
    /**
     * Canonical reason codes (DB / API). Order matches UGC report flows.
     *
     * @return list<string>
     */
    public static function valueKeys(): array
    {
        return [
            'off_platform_requests',
            'misleading_profile_or_skills',
            'no_show_or_unreliability',
            'poor_work_quality',
            'unprofessional_behavior',
            'fraud_or_misleading_information',
            'overcharging_or_hidden_fees',
            'incomplete_or_abandoned_work',
            'harassment_or_bullying',
            'spam_or_scams',
            'hate_speech',
            'violence_or_threats',
            'payment_issues',
            'overcharging_or_extra_fees',
            'violation_of_platform_rules',
            'plagiarism_or_copied_work',
            'nudity_or_sexual_content',
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::valueKeys() as $value) {
            $out[] = ['value' => $value, 'label' => self::label($value)];
        }

        return $out;
    }

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
