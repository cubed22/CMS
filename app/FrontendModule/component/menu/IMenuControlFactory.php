<?php

namespace App\Components;

/**
 * Factory interface for creating MenuControl instances.
 */
interface IMenuControlFactory
{
    /**
     * Create a new instance of MenuControl.
     *
     * @return MenuControl
     */
    public function create(): MenuControl;
}