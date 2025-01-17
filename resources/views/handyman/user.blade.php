@if(isset($query->id) && $query->status == 1)
  <a href="{{ route('handyman.detail', ['id' => $query->id]) }}">
    <div class="d-flex gap-3 align-items-center">
      <img src="{{ getSingleMedia($query,'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
      <div class="text-start">
        <h6 class="m-0">{{ $query->display_name }} </h6>
        <span>{{ $query->country->name ?? '--' }}</span> -
        <span>{{ $query->city->name ?? '--' }}</span>
      </div>
    </div>
  </a>
@elseif(isset($query->id))
  <a href="#">
    <div class="d-flex gap-3 align-items-center">
      <img src="{{ getSingleMedia($query,'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
      <div class="text-start">
        <h6 class="m-0">{{ $query->display_name }} </h6>
        <span>{{ $query->country->name ?? '--' }}</span> -
        <span>{{ $query->city->name ?? '--' }}</span>
      </div>
    </div>
  </a>
@else 

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
  </div>
@endif




