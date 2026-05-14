<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for handling static pages in the frontend, allowing users to view page details based on URL.
 */
final class PagePresenter extends FrontendPresenter
{
	/** @var Model\Pages */
	private Model\Pages $pages;

	/**
	 * Page presenter constructor.
	 *
	 * @param Model\Pages $pages
	 * @return void
	 */
	public function __construct(Model\Pages $pages)
	{
		parent::__construct();
		$this->pages = $pages;
	}

	/**
	 * Default action to list all active pages for the site.
	 *
	 * @return void
	 */
	public function actionDefault() 
	{
		$this->template->items = $this->pages->findAll(["active" => 1], 'time DESC');
	}

	/**
	 * Detail action to display a specific page based on its URL.
	 *
	 * @param string $url
	 * @return void
	 */
	public function actionDetail($url) 
	{
		$record = $this->pages->findByUrl($url);
		if (!$record) 
			$this->error("Stránka nenalezena.");

		if (!$record->data()->active)
			$this->error("Stránka nenalezena.");
  	
		$this->template->record = $record;
		$this->template->images = $record->images();
	}
}
