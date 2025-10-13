<button type="button" style="border-radius: 22px !important;" @if ($disabled) disabled @endif {{ $attributes->merge(['class' => 'btn btn-primary rounded-pill f-14 p-2']) }}>
    @if ($icon != '')
        <i class="fa fa-{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</button>

@include('sections.password-autocomplete-hide')
