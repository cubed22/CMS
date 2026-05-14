<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use Nette;

/**
 * Presenter for handling 4xx HTTP errors in the frontend, displaying appropriate error pages based on the exception code.
 */
final class Error4xxPresenter extends FrontendPresenter
{
	/**
	 * Startup lifecycle hook to ensure this presenter is only used for forwarded requests.
	 *
	 * @return void
	 */
	public function startup(): void
	{
		parent::startup();
		if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
			$this->error();
		}
	}

	/**
	 * Render method to display the appropriate error page based on the exception code.
	 *
	 * @param Nette\Application\BadRequestException $exception
	 * @return void
	 */
	public function renderDefault(Nette\Application\BadRequestException $exception): void
	{
		// load template 403.latte or 404.latte or ... 4xx.latte
		$file = __DIR__ . "/../templates/Error/{$exception->getCode()}.latte";
		$this->template->setFile(is_file($file) ? $file : __DIR__ . '/../templates/Error/4xx.latte');
	}

}
