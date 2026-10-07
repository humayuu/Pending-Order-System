@props(['icon' => 'fa-inbox', 'message' => 'Nothing here yet.'])
<div class="text-center text-body-secondary py-5 px-3">
    <i class="fa-solid {{ $icon }} fa-2x text-body-tertiary d-block mb-2" aria-hidden="true"></i>
    <p class="mb-3">{{ $message }}</p>
    {{ $slot }}
</div>
