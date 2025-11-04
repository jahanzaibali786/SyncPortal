<?php

namespace App\View\Components\Forms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datepicker extends Component
{
    public $fieldLabel;
    public $fieldRequired;
    public $fieldPlaceholder;
    public $fieldValue;
    public $fieldName;
    public $fieldId;
    public $fieldHelp;
    public $custom;
    public $popover;

    public function __construct(
        $fieldLabel = null,
        $fieldPlaceholder = null,          // <-- now optional
        $fieldName = null,
        $fieldId = null,
        $fieldRequired = false,
        $fieldValue = null,
        $fieldHelp = null,
        $custom = false,
        $popover = null
    ) {
        $this->fieldLabel       = $fieldLabel;
        $this->fieldRequired    = (bool) $fieldRequired;
        $this->fieldPlaceholder = $fieldPlaceholder ?? __('placeholders.date'); // <-- default text
        $this->fieldValue       = $fieldValue;
        $this->fieldName        = $fieldName;
        $this->fieldId          = $fieldId;
        $this->fieldHelp        = $fieldHelp;
        $this->custom           = (bool) $custom;
        $this->popover          = $popover;
    }

    public function render(): View|string
    {
        return view('components.forms.datepicker');
    }
}
