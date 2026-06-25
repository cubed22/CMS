<?php

namespace App\Components;

use Nette\ComponentModel\IContainer;
use App\AdminModule\Translator\AdminTranslator;

class AdminGrid extends \Ublaboo\DataGrid\DataGrid
{
    use \Nette\SmartObject;

    public function __construct(?IContainer $parent = null, ?string $name = null)
    {
        parent::__construct($parent, $name);

        $translator = new AdminTranslator();

        $this->setTranslator($translator);
    }
}