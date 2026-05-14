<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

/**
 * Bootstrap class for initializing the application with configuration settings, including database connections, routing, and other services.
 */
class Bootstrap
{
	/**
	 * Boot the application by creating a Configurator instance, setting up debugging, timezone, temp directory, robot loader, and loading configuration files.
	*
	* @return Configurator The configured Configurator instance ready to create the application.
	*/
	public static function boot(): Configurator
	{
		$configurator = new Configurator; 
		$appDir = dirname(__DIR__);

		$configurator->setDebugMode( true );
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig($appDir . '/app/config/common.neon');
		$configurator->addConfig($appDir . '/app/config/services.neon');
		$configurator->addConfig($appDir . '/app/config/local.neon');
		//$configurator->addConfig($appDir . '/app/config/production.neon');

		return $configurator;
	}
}
