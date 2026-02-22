<?php

namespace App\View\Components;

use App\Models\Sale\SaleReturn;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropdownSalesReturn extends Component
{
    /**
     * Roles array
     *
     * @var array
     */
    public $salesReturn;

    /**
     * Selected option
     *
     * @var string
     */
    public $selected;

    /**
     * Create a new component instance.
     */
    public function __construct($selected = null)
    {
        $this->salesReturn  = SaleReturn::get();
        $this->selected = $selected;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dropdown-sales-return');
    }
}
