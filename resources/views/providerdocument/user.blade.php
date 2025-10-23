@if(isset($query->providers))
@php
    $allowEdit = auth()->user()->can('providerdocument edit') && !(auth()->user()->hasRole('provider') && (int)($query->is_verified) === 1);
@endphp
@if($allowEdit)
<a href="{{ route('providerdocument.create', ['id' => $query->id,'providerdocument' => $query->provider_id]) }}">
  <div class="d-flex gap-3 align-items-center">
    <img src="{{ getSingleMedia(optional($query->providers),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
    <div class="text-start">
      <h6 class="m-0">{{ optional($query->providers)->display_name }} </h6>
      <span>{{ optional($query->providers)->country->name ?? '--' }} - {{ optional($query->providers)->city->name ?? '--' }}</span>
    </div>
  </div>
</a>
@else

 <div class="d-flex gap-3 align-items-center">
    <img src="{{ getSingleMedia(optional($query->providers),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
    <div class="text-start">
      <h6 class="m-0 tn-link btn-link-hover">{{ optional($query->providers)->display_name }} </h6>
      <span>{{ optional($query->providers)->country->name ?? '--' }} - {{ optional($query->providers)->city->name ?? '--' }}</span>
    </div>
  </div>

  @endif
  @else
  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
  @endif




