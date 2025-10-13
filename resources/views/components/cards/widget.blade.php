<div {{ $attributes->merge(['class' => 'bg-white p-20 d-flex align-items-center xcardswidget']) }}>
    <div class="iconbox">
        <i class="fa f-35 fa-{{ $icon }}"></i>
    </div>
    <div class="contentbox">
        <span class="f-13 text-lightest">{{ $title }}
            @if (!is_null($info))
                <i class="fa fa-question-circle" data-toggle="popover" data-placement="top"
                    data-content="{{ $info }}" data-html="true" data-trigger="hover"></i>
            @endif
        </span>
        <div class="d-flex">
            <h2 class="mb-0 font-weight-bold text-dark d-grid" id="{{ $widgetId }}">
                {{ $value }}
            </h2>
        </div>
    </div>
</div>
