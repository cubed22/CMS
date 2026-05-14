<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for displaying email logs in the frontend, allowing users to view details of sent emails.
 */
final class EmailWebPresenter extends FrontendPresenter
{
	/** @var Model\LogMessages */
	private Model\LogMessages $logMessages;

	/**
	 * EmailWeb presenter constructor.
	 *
	 * @param Model\LogMessages $logMessages
	 * @return void
	 */
	public function __construct(Model\LogMessages $logMessages)
	{
		parent::__construct();
	  	$this->logMessages = $logMessages;
	}

	/**
	 * Default action to display details of a specific email log entry.
	 *
	 * @param int $id
	 * @return void
	 */
	public function actionDetail(int $id)
	{
		$record = $this->logMessages->find($id);
		if (!$record) {
			$this->error("Neplatný požadavek.");
		}
		$this->template->record = $record;
	}

}
