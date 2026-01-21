@if(isset($query->service))
  <span>{{ optional(optional($query->service)->country)->name ?? '--' }}-{{ optional(optional($query->service)->city)->name ?? '--' }}</span>
@elseif(isset($row))
  <span>{{ $row['country'] ?? '--' }}-{{ $row['city'] ?? '--' }}</span>
@else
  <span>--</span>
@endif


