@if(isset($query->customer))
<a href="{{ route('user.show', optional($query->customer)->id) }}">
  <div class="d-flex gap-3 align-items-center">
    <img src="{{ getSingleMedia(optional($query->customer),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
    <div class="text-start">
      <h6 class="m-0">{{ optional($query->customer)->display_name }}</h6>
      <span class="small text-muted">{{ optional($query->customer)->email }}</span>
    </div>
  </div>
</a>
@else
  <div class="align-items-center">
    <h6 class="text-center mb-0">—</h6>
  </div>
@endif
