<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

/**
 * Factory for creating the router with dynamic routes based on database entries for blog posts, categories, pages, and legal documents.
 */
final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(
		\App\Model\Blog $blog, 
		\App\Model\BlogCategories $blogCategories,
		\App\Model\TermsConditions $termsConditions,
		\App\Model\PersonalDataProtections $pdp,
        \App\Model\Pages $pages,
        \App\Model\LanguageModel $languages,
	): RouteList 
	{
		$router = new RouteList;
		
		$router->withModule('Admin') 
			->addRoute('admin/[<lang=cz en|cz|sk>/]<presenter>/<action>[/<id>]', 'Homepage:default');

		$router->withModule('Frontend')
			->addRoute('[<lang=cz en|cz|sk>/]', 'Homepage:default')
            ->addRoute('cron', 'Cron:default')
            
            ->addRoute('[<lang=cz en|cz|sk>/]blog[/<category>]', 'Blog:default')

            // prihlaseni a registrace
            ->addRoute('prihlaseni', 'Sign:in')
            ->addRoute('zapomenute-heslo', 'Sign:lost')
            ->addRoute('nastavit-nove-heslo/<hash>', 'Sign:setNewPassword')
            ->addRoute('registrace', 'Register:default')

            ->addRoute('emailWeb/<id>', 'EmailWeb:detail');

        foreach ($languages->findAll() as $language) {
            $shortcut = $language->data()->shortcut;
            foreach ($blog->findAll($shortcut, ['url NOT ?' => NULL]) as $item) {
                $router->addRoute('[<lang=cz>/]' . $item->locale($shortcut)->url, [
                    'module'    => 'Frontend',
                    'presenter' => 'Blog',
                    'action'    => 'detail',
                    'lang'      => $shortcut,
                    'url'       => $item->locale($shortcut)->url
                ]);
            }
        }

        foreach ($blogCategories->findAll(['url NOT ?' => NULL]) as $item) {
            $router->addRoute($item->data()->url, [
                'module'    => 'Frontend',
                'presenter' => 'BlogCategory',
                'action'    => 'detail',
                'url'       => $item->data()->url
            ]);
        }

        foreach ($pages->findAll(['url NOT ?' => NULL]) as $item) {
            $router->addRoute($item->data()->url, [
                'module'    => 'Frontend',
                'presenter' => 'Page',
                'action'    => 'detail',
                'url'       => $item->data()->url
            ]);
        }
        
        foreach ($termsConditions->findAll(['url NOT ?' => NULL]) as $item) {
            $router->addRoute($item->data()->url, [
                'module'    => 'Frontend',
                'presenter' => 'TermsCondition',
                'action'    => 'detail',
                'url'       => $item->data()->url
            ]);
        }
        
        foreach ($pdp->findAll(['url NOT ?' => NULL]) as $item) {
            $router->addRoute($item->data()->url, [
                'module'    => 'Frontend',
                'presenter' => 'PersonalDataProtection',
                'action'    => 'detail',
                'url'       => $item->data()->url
            ]);
        }

		return $router;
	}
}
