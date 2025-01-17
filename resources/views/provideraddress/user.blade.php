@if(isset($query->providers))
@if(auth()->user()->can('provideraddress edit'))
<a href="{{ route('provideraddress.create', ['id' => $query->id,'provideraddress' => $query->provider_id]) }}">
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
    <div class="text-start ">
      <h6 class="m-0 btn-link btn-link-hover">{{ optional($query->providers)->display_name }} </h6>
      <span class="btn-link btn-link-hover">{{ optional($query->providers)->country->name ?? '--' }} - {{ optional($query->providers)->city->name ?? '--' }}</span>
    </div>
  </div>

  @endif
  @else
  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
  @endif





