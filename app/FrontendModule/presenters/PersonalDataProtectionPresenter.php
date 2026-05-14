<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for handling personal data protection pages in the frontend, allowing users to view details based on URL.
 */
final class PersonalDataProtectionPresenter extends FrontendPresenter
{
	/** @var Model\PersonalDataProtections */
	private Model\PersonalDataProtections $model;

	/**
	 * PersonalDataProtection presenter constructor.
	 *
	 * @param Model\PersonalDataProtections $model
	 * @return void
	 */
	public function __construct(Model\PersonalDataProtections $model)
	{
		parent::__construct();
		$this->model = $model;
	}

	/**
	 * Detail action to display a specific personal data protection page based on its URL.
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
