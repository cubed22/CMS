<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use App\Model;

/**
 * Presenter for admin panels and shared template data.
 */
class AdminPresenter extends Presenter
{
    /** @persistent */
    public string $lang;

    /** @var Model\Users */
    private Model\Users $users;

    /** @var Model\Pages */
    private Model\Pages $pages;

    /** @var Model\Blog */
    private Model\Blog $blog;

    /** @var Model\BlogCategories */
    private Model\BlogCategories $blogCategories;

    /** @var Model\Cronmaster */
    private Model\Cronmaster $cron;

    /** @var Model\Messages */
    private Model\Messages $messages;

    /** @var Model\Settings */
    private Model\Settings $settings;

    /** @var Model\Menu */
    private Model\Menu $menu;

    /** @var Model\Slideshow */
    private Model\Slideshow $slideshow;

    /** @var Model\LanguageModel */
    private Model\LanguageModel $languages;

    /** @var Model\Url */
    private Model\Url $url;

    /**
     * Inject the settings model.
     *
     * @param Model\Settings $settings
     * @return void
     */
    public function injectSettings(Model\Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Model\Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Inject the users model.
     *
     * @param Model\Users $users
     * @return void
     */
    public function injectUsers(Model\Users $users)
    {
        $this->users = $users;
    }

    /**
     * @return Model\Users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Inject the pages model.
     *
     * @param Model\Pages $pages
     * @return void
     */
    public function injectPages(Model\Pages $pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return Model\Pages
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Inject the blog model.
     *
     * @param Model\Blog $blog
     * @return void
     */
    public function injectBlog(Model\Blog $blog)
    {
        $this->blog = $blog;
    }

    /**
     * @return Model\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Inject the blog categories model.
     *
     * @param Model\BlogCategories $blogCategories
     * @return void
     */
    public function injectBlogCategories(Model\BlogCategories $blogCategories)
    {
        $this->blogCategories = $blogCategories;
    }

    /**
     * @return Model\BlogCategories
     */
    public function getBlogCategories()
    {
        return $this->blogCategories;
    }

    /**
     * Inject the cronmaster model.
     *
     * @param Model\Cronmaster $cron
     * @return void
     */
    public function injectCronmaster(Model\Cronmaster $cron)
    {
        $this->cron = $cron;
    }

    /**
     * @return Model\Cronmaster
     */
    public function getCronmaster()
    {
        return $this->cron;
    }

    /**
     * Inject the messages model.
     *
     * @param Model\Messages $messages
     * @return void
     */
    public function injectMessages(Model\Messages $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return Model\Messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Inject the menu model.
     *
     * @param Model\Menu $menu
     * @return void
     */
    public function injectMenu(Model\Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return Model\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Inject the slideshow model.
     *
     * @param Model\Slideshow $slideshow
     * @return void
     */
    public function injectSlideshow(Model\Slideshow $slideshow)
    {
        $this->slideshow = $slideshow;
    }

    /**
     * @return Model\Slideshow
     */
    public function getSlideshow()
    {
        return $this->slideshow;
    }

    /**
     * Inject the languages model.
     *
     * @param Model\LanguageModel $languages
     * @return void
     */
    public function injectLanguages(Model\LanguageModel $languages)
    {
        $this->languages = $languages;
    }

    /**
     * @return Model\LanguageModel
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Inject the URL model.
     *
     * @param Model\Url $url
     * @return void
     */
    public function injectUrl(Model\Url $url)
    {
        $this->url = $url;
    }

    /**
     * @return Model\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    public function locale()
    {
        return $this->lang;
    }

    /**
     * Get all available languages.
     *
     * @return array
     */
    public function getAllLanguages()
    {
        return $this->getLanguages()->findAll();
    }

    /**
     * Handle WYSIWYG image upload.
     *
     * @return void
     */
    public function actionWysiwygInsertImage(): void
    {
        /** @var FileUpload $insertedImage */
        $insertedImage = $this->getHttpRequest()->getFiles()['file'];

        $pathToImage = $this->getHttpRequest()->getUrl()->getBasePath() . 'www/upload/';
        $uploadPath = __DIR__ . '/../../../www/upload/';

        /** @var string $uniqid */
        $uniqid = uniqid();
        /** @var string $fileNameImage */
        $fileNameImage = $uniqid . '-' . $insertedImage->getName();

        $insertedImage->move($uploadPath . $fileNameImage);

        $link = $pathToImage . $fileNameImage;
        $this->sendJson([
            'url' => $link,
        ]);
    }

    /**
     * Prepare common data for admin templates.
     *
     * @return void
     */
    public function beforeRender(): void
    {
        // localization handling
        $session = $this->getSession()->getSection('adminLang');
        $lang = $this->getParameter('lang') ?? $session->lang ?? 'cz';
        $session->lang = $lang;
        $this->lang = $lang;

        $user = $this->getAdminUser();
        $this->template->adminUser = $user;

        $this->template->allPageItems = $this->getPages()->findAll();
        $this->template->allBlogItems = $this->getBlog()->findAll();
        $this->template->allBlogCategoryItems = $this->getBlogCategories()->findAll();
        $this->template->allUserItems = $this->getUsers()->findAll();
        $this->template->allCronItems = $this->getCronmaster()->findAll();
        $this->template->allMessageItems = $this->getMessages()->findAll();
        $this->template->allMenuItems = $this->getMenu()->findAll();
        $this->template->allSlideshowItems = $this->getSlideshow()->findAll();

        $this->template->settingRecord = $this->getSettings()->find(1);
	}

	public function handleLogout(): void
    {
		if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
            $this->flashMessage("Odhlášení proběhlo úspěšně.");
            $this->redirect('Sign:in');
        }   
	}

    /**
     * Get the admin user.
     *
     * @return Model\UserRecord|null
     */
    public function getAdminUser(): ?Model\UserRecord
    {
        $user = null;
        if ($this->getUser()->isLoggedIn()) {
        $userId = $this->getUser()->getId();

        /** @var Model\UserRecord|false $user */
        $user = $this->users->find($userId);
        if ($user === false) {
            $this->getUser()->logout();
            $this->redirect('Sign:in');
        }
        }
        return $user;
    }

    /**
     * Initialize admin session namespace and ensure admin access.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();

        /** @var Nette\Bridges\SecurityHttp\SessionStorage $storage */
        $storage = $this->getUser()->getStorage();
        $storage->setNamespace('admin');
        
        if ($this->getUser()->isLoggedIn()) {
            $userId = $this->getUser()->getId();
            
            /** @var Model\UserRecord|false $user */
            $user = $this->getUsers()->find($userId);
            if ($user === false || !$user->isAdmin()) {
                $this->redirect('Sign:in');
            }
        } else {
            $this->redirect('Sign:in');
        }
    }

    /**
     * Create the admin CSS loader component.
     *
     * @return \WebLoader\Nette\CssLoader
     */
    protected function createComponentAdminCss(): \WebLoader\Nette\CssLoader
    {
        $wwwDir = './www/admin/css';
        $contentTmpDir = './www/admin/cache';

        $files = new \WebLoader\FileCollection($wwwDir);

        $files->addFiles(array('main.css'));
        $files->addFiles(array('datagrid.css'));

        $compiler = \WebLoader\Compiler::createCssCompiler($files, $contentTmpDir);

        $compiler->addFilter(function ($code) {
            return \CssMin::minify($code);
        });

        $control = new \WebLoader\Nette\CssLoader($compiler, $this->getHttpRequest()->getUrl()->basePath . 'www/admin/cache');
        $control->setMedia('screen');

        return $control;
    }

    /**
     * Parse localized values from form submission.
     *
     * @param \Nette\Utils\ArrayHash &$values
     * @return array
     */
    public function parseLocalizedValues(\Nette\Utils\ArrayHash &$values)
    {
        $locals = [];
        foreach ($values as $key => $value) {
            if (strpos($key, "_") !== false) {
                $parts = explode("_", $key, 2);
                $lang = $parts[0];
                $field = $parts[1];
                unset($values[$key]);
                if (!isset($locals[$lang])) {
                    $locals[$lang] = [];
                }
                $locals[$lang][$field] = $value;
            }
        }
        return $locals;
    }

    /**
     * Verify and sanitize URLs in localized values.
     *
     * @param Model\LocalizedRecord $record
     * @param mixed &$locals
     * @return void
     */
    public function verifyUrl($record, mixed &$locals) 
    {
        foreach ($locals as $lang => &$localValues) {
            if (isset($localValues['url'])) {
                if ($record->locale($lang)->url != $localValues['url']) {
                    $localValues['url'] = $this->getUrl()->getUrl($localValues['url']);
                }
            }
        }
    }
}