<?php

namespace App\Components;

use Nette\Application\UI\Control;

/**
 * PaginationControl is a UI component for rendering pagination controls in the frontend.
 */
class PaginationControl extends Control
{
    /** @var int */
    public int $page = 1; 
    
    /** @var int */
    public int $itemsPerPage = 2; 

    /** @var int */
    public int $totalItems = 0;

    /**
     * Render the pagination control.
     *
     * @return void
     */
    public function render(): void
    {
        $totalPages = max(1, (int) ceil($this->totalItems / $this->itemsPerPage));

        $this->template->setFile(__DIR__ . '/templates/PaginationControl.latte');
        $this->template->page = $this->page;
        $this->template->totalPages = $totalPages;
        $this->template->render();
    }

    /**
     * Handle page change requests.
     *
     * @param int $page The new page number.
     * @param mixed $category The category parameter for the link.
     * @return void
     */
    public function handlePage(int $page, $category): void
    {
        $this->page = max(1, $page); // Zajištění minimální stránky

        $this->getPresenter()->redrawControl('pagination');
        $this->getPresenter()->redrawControl('itemsSnippet'); // Redraw snippetu s položkami

        $this->getPresenter()->payload->url = $this->getPresenter()->link('this', ["pagination-page" => $this->page, "do" => "pagination-page"]);
    }
}
