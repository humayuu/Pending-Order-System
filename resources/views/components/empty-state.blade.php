@props(['icon' => 'bi-inbox', 'message' => 'Nothing here yet.'])
<div class="empty-state">
    <i class="bi {{ $icon }}" aria-hidden="true"></i>
    <p>{{ $message }}</p>
    {{ $slot }}
</div>
