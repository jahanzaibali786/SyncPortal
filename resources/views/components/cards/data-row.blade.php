@if (!$html)
    <div class="col-12 px-0 pb-2 d-lg-flex d-md-flex d-block">
        <p class="mb-0 text-lightest f-13 w-30 {{ $labelClasses }}">{{ $label }}</p>
        <div class="mb-0 text-dark-grey f-13 w-70 text-wrap {{ $otherClasses }}">
            {{-- Prefer slot content if provided, else show value --}}
            @if (trim($slot))
                {{ $slot }}
            @else
                {!! $value !!}
            @endif
        </div>
    </div>
@else
    <div class="col-12 px-0 pb-2 d-lg-flex d-md-flex d-block">
        <p class="mb-0 text-lightest f-13 w-30 {{ $labelClasses }}">{{ $label }}</p>
        <div class="mb-0 text-dark-grey f-13 w-70 text-wrap ql-editor p-0 {{ $otherClasses }}">
            @if (trim($slot))
                {{ $slot }}
            @else
                {!! nl2br($value) !!}
            @endif
        </div>
    </div>
@endif
