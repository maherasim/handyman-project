@php
    $ugcReasonKeysOrdered = [
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
    $ugcReasonLabels = \Illuminate\Support\Facades\Lang::get('messages.profile_report_reasons', [], app()->getLocale());
    if (! is_array($ugcReasonLabels)) {
        $ugcReasonLabels = [];
    }
    $ugcReasonOptionsForJs = array_values(array_map(function ($key) use ($ugcReasonLabels) {
        return [
            'value' => $key,
            'label' => $ugcReasonLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
        ];
    }, $ugcReasonKeysOrdered));
@endphp
