<?php
namespace App\View\Components;

use Illuminate\View\Component;

class SubMenuDropdown extends Component
{
    public $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function render()
    {
        return view('components.sub-menu-dropdown');
    }
}
