<?php
namespace App\View\Components\Cards;

use Illuminate\View\Component;

class DataRow extends Component
{
    public $label;
    public $value;
    public $html;
    public $otherClasses;
    public $labelClasses;

    public function __construct(
        $label,
        $value = null,
        $html = false,
        $otherClasses = '',
        $labelClasses = ''
    ) {
        $this->label = $label;
        $this->value = $value;
        $this->html = $html;
        $this->otherClasses = $otherClasses;
        $this->labelClasses = $labelClasses;
    }

    public function render()
    {
        return view('components.cards.data-row');
    }
}
