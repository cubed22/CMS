<?php

namespace App\Components;

/**
 * Interface for creating pagination controls.
 */
interface IPaginationControlFactory
{
    public function create(): PaginationControl;
}