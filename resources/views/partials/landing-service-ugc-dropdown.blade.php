{{-- Service card ⋮ report / block (landing home: top rated + featured). Expects $service model with ->id and ->providers. --}}
@once
<style>
    .service-box-card .dropdown-toggle-no-caret::after { display: none !important; }
</style>
@endonce
@php
    $svc = $service;
    $ugcProvId = optional($svc->providers)->id;
@endphp
@if(auth()->check() && \App\Support\UgcListing::canUseFrontendReportMenu(auth()->user()) && $ugcProvId && (int) auth()->id() !== (int) $ugcProvId)
<div class="dropdown mt-2 text-end px-2 pb-2">
   <button type="button" class="btn btn-link text-muted text-decoration-none p-1 border-0 bg-transparent dropdown-toggle-no-caret" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="{{ __('messages.ugc_report') }}" style="padding: 4px 8px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
         <circle cx="5" cy="12" r="2" fill="currentColor"/>
         <circle cx="12" cy="12" r="2" fill="currentColor"/>
         <circle cx="19" cy="12" r="2" fill="currentColor"/>
      </svg>
   </button>
   <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 150px; font-size: 14px;">
      <li>
         <a class="dropdown-item text-secondary py-2" href="javascript:void(0)"
            onclick="if(window.triggerUgcReport) window.triggerUgcReport({{ $svc->id }}, this);">
            <i class="fas fa-flag fa-fw me-2"></i>{{ __('messages.ugc_report') }}
         </a>
      </li>
      <li>
         <a class="dropdown-item text-danger py-2" href="javascript:void(0)"
            onclick="if(window.triggerUgcBlock) window.triggerUgcBlock({{ $ugcProvId }}, this, 'employer');">
            <i class="fas fa-ban fa-fw me-2"></i>{{ __('messages.ugc_block_employer') }}
         </a>
      </li>
   </ul>
</div>
@endif
