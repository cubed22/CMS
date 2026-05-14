<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use Nette;
use Nette\Application\Responses;
use Nette\Http;
use Tracy\ILogger;

/**
 * Presenter for handling application errors in the frontend, logging exceptions and displaying appropriate error pages.
 */
final class ErrorPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

	/** @var ILogger */
	private ILogger $logger;

	/**
	 * Error presenter constructor.
	 *
	 * @param ILogger $logger
	 * @return void
	 */
	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Run method to handle incoming requests and generate appropriate responses based on the exception type.
	 *
	 * @param Nette\Application\Request $request
	 * @return Nette\Application\Response
	 */
	public function run(Nette\Application\Request $request): Nette\Application\Response
	{
		$exception = $request->getParameter('exception');

		if ($exception instanceof Nette\Application\BadRequestException) {
			[$module, , $sep] = Nette\Application\Helpers::splitName($request->getPresenterName());
			return new Responses\ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
		}

		$this->logger->log($exception, ILogger::EXCEPTION);
		return new Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
			if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/../templates/Error/500.phtml';
			}
		});
	}
}
