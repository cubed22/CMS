<?php

namespace App\Components;

use Nette\Application\UI\Control;

/**
 * Control for rendering a menu based on a given label.
 */
class MenuControl extends Control
{
  /**
   * Render the menu based on the label parameter.
   * 
   * @param string $label The label of the menu to render
   * @return void
   */
  public function render($label): void
  {
    $menu = $this->getPresenter()->getMenu()->findByPropertyOne("label", $label);

    $this->template->setFile(__DIR__ . '/templates/' . $label . '.latte');
    $this->template->items = $menu->items(null, "position");
    $this->template->render();
  }
}
