<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;

/**
 * Presenter for handling cron jobs in the frontend, executing scheduled tasks.
 */
final class CronPresenter extends FrontendPresenter
{
	/** @var Model\Cronmaster */
	private Model\Cronmaster $cron;

	/**
	 * Cron presenter constructor.
	 *
	 * @param Model\Cronmaster $cron
	 * @return void
	 */
	public function __construct(Model\Cronmaster $cron)
	{
		parent::__construct();
		$this->cron = $cron;
	}

	/**
	 * Default action to execute due cron jobs.
	 *
	 * @return void
	 */
	public function actionDefault() 
	{
		$where = [];
		$where["active"] = 1;
		$where["next_exec < ?"] = time();

		$cronJobs = $this->cron->findAll($where);
		foreach ($cronJobs as $job) {

			$jobMethod = $job->data()->method;
			$this->{$jobMethod}();

			$nextExec = strtotime('tomorrow ' . $job->data()->hour . ':' . $job->data()->minute);
			$job->update(["last_exec" => time(), "next_exec" => $nextExec]);
		}
	}
}