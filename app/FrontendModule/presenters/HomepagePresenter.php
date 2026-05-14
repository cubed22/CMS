<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for the homepage of the frontend, responsible for displaying the main content and slideshow items.
 */
final class HomepagePresenter extends FrontendPresenter
{
	/** @var Model\Slideshow */
	private Model\Slideshow $slideshow;

	/**
	 * Homepage presenter constructor.
	 *
	 * @param Model\Slideshow $slideshow
	 * @return void
	 */
	public function __construct(Model\Slideshow $slideshow)
	{
		parent::__construct();
		$this->slideshow = $slideshow;
	}

	/**
	 * Default action to prepare slideshow items for the homepage template.
	 *
	 * @return void
	 */
	public function actionDefault()
	{
		$this->template->slideshowItems = $this->slideshow->findAll();
	}
}
