<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for handling terms and conditions pages in the frontend, allowing users to view details based on URL.
 */
final class TermsConditionPresenter extends FrontendPresenter
{
	/** @var Model\TermsConditions */
	private Model\TermsConditions $model;

	/**
	 * TermsCondition presenter constructor.
	 *
	 * @param Model\TermsConditions $model
	 * @return void
	 */
	public function __construct(Model\TermsConditions $model)
	{
		parent::__construct();
		$this->model = $model;
	}

	/**
	 * Detail action to display a specific terms and conditions page based on its URL.
	 *
	 * @param string $url
	 * @return void
	 */
	public function actionDetail($url) 
	{
		$record = $this->model->findByUrl($url);
		if (!$record) 
			$this->error("Nenalezeno.");
  		
  		$this->template->record = $record;
	}
}
