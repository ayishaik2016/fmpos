<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BrowseAttachment extends Component
{
    /**
     * Attachment Source URL
     * 
     * @var string
     */
    public $src;

    /**
     * Attribute name
     * 
     * @var string
     */
    public $name;

    /**
     * Attribute name
     * 
     * @var string
     */
    public $attachmentid;

    /**
     * Attribute name
     * 
     * @var string
     */
    public $inputBoxClass;

    /**
     * Attribute name
     * 
     * @var string
     */
    public $attachmentResetClass;

    /**
     * Create a new component instance.
     */
    public function __construct($src, $name, $attachmentid=null, $inputBoxClass=null, $attachmentResetClass)
    {
        $this->src = $src;
        $this->name = $name;
        $this->attachmentid = $attachmentid;
        $this->inputBoxClass = $inputBoxClass;
        $this->attachmentResetClass = $attachmentResetClass;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.browse-attachment');
    }
}
