<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use Nette;
use App\Model;
use App\Components;
use Nette\Application\UI\Form;
use App\FrontendModule\Authenticator\FrontendAuthenticator;
use App\FrontendModule\Translator\FrontendTranslator;

/**
 * Base presenter for the frontend module, providing common dependencies and functionality for all frontend presenters.
 */
class FrontendPresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public string $lang;

    /** @var FrontendAuthenticator Frontend authenticator for user authentication */
    private FrontendAuthenticator $frontAuthenticator;

	/** @var Model\Blog */
	private Model\Blog $blog;

    /** @var Model\TermsConditions  */
    private Model\TermsConditions $termsConditions;

    /** @var Model\PersonalDataProtections  */
    private Model\PersonalDataProtections $personalDataProtections;

    /** @var Model\Settings */
    private Model\Settings $settings;

    /** @var Nette\Security\Passwords */
    private Nette\Security\Passwords $passwords;

    /** @var Model\Mailer */
    private Model\Mailer $mailer;

    /** @var Model\Url */
    private Model\Url $url;

    /** @var Model\Pages */
    private Model\Pages $pages;

    /** @var Model\Menu */
    private Model\Menu $menu;

    /** @var Model\Modules */
    private Model\Modules $modules;

    /** @var FrontendTranslator  */
    private FrontendTranslator $translator;

    /** @var Components\IMenuControlFactory */
    private Components\IMenuControlFactory $menuControl;

    /**
     * Inject frontend translator dependency.
     *
     * @param FrontendTranslator $translator
     * @return void
     */
    public function injectFrontendTranslator( FrontendTranslator $translator )
    {
        $this->translator = $translator;
    }

    /**
     * Get injected frontend translator.
     *
     * @return FrontendTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Inject frontend authenticator dependency.
     *
     * @param FrontendAuthenticator $frontAuthenticator
     * @return void
     */
    public function injectFrontendAuthenticator( FrontendAuthenticator $frontAuthenticator )
    {
        $this->frontAuthenticator = $frontAuthenticator;
    }

    /**
     * Get injected frontend authenticator.
     *
     * @return FrontendAuthenticator
     */
    public function getAuthenticator()
    {
        return $this->frontAuthenticator;
    }

    /**
     * Inject menu control factory.
     *
     * @param Components\IMenuControlFactory $menuControl
     * @return void
     */
    public function injectMenuControl( Components\IMenuControlFactory $menuControl )
    {
        $this->menuControl = $menuControl;
    }

    /**
     * Get injected menu control factory.
     *
     * @return Components\IMenuControlFactory
     */
    public function getMenuControl()
    {
        return $this->menuControl;
    }

    /**
     * Inject password hashing service.
     *
     * @param Nette\Security\Passwords $passwords
     * @return void
     */
    public function injectPasswords( Nette\Security\Passwords $passwords )
    {
        $this->passwords = $passwords;
    }

    /**
     * Get injected password hashing service.
     *
     * @return Nette\Security\Passwords
     */
    public function getPasswords()
    {
        return $this->passwords;
    }

    /**
     * Inject blog model dependency.
     *
     * @param Model\Blog $blog
     * @return void
     */
    public function injectBlog( Model\Blog $blog )
    {
        $this->blog = $blog;
    }

    /**
     * Get injected blog model.
     *
     * @return Model\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Inject terms and conditions model.
     *
     * @param Model\TermsConditions $termsConditions
     * @return void
     */
    public function injectTermsConditions( Model\TermsConditions $termsConditions )
    {
        $this->termsConditions = $termsConditions;
    }

    /**
     * Get injected terms and conditions model.
     *
     * @return Model\TermsConditions
     */
    public function getTermsConditions()
    {
        return $this->termsConditions;
    }

    /**
     * Inject personal data protections model.
     *
     * @param Model\PersonalDataProtections $personalDataProtections
     * @return void
     */
    public function injectPersonalDataProtections( Model\PersonalDataProtections $personalDataProtections )
    {
        $this->personalDataProtections = $personalDataProtections;
    }

    /**
     * Get injected personal data protections model.
     *
     * @return Model\PersonalDataProtections
     */
    public function getPersonalDataProtections()
    {
        return $this->personalDataProtections;
    }

    /**
     * Inject settings model.
     *
     * @param Model\Settings $settings
     * @return void
     */
    public function injectSettings( Model\Settings $settings )
    {
        $this->settings = $settings;
    }

    /**
     * Get injected settings model.
     *
     * @return Model\Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Inject mailer model.
     *
     * @param Model\Mailer $mailer
     * @return void
     */
    public function injectMailer( Model\Mailer $mailer )
    {
        $this->mailer = $mailer;
    }

    /**
     * Get injected mailer model.
     *
     * @return Model\Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Inject URL model.
     *
     * @param Model\Url $url
     * @return void
     */
    public function injectUrl( Model\Url $url )
    {
        $this->url = $url;
    }

    /**
     * Get injected URL model.
     *
     * @return Model\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Inject pages model.
     *
     * @param Model\Pages $pages
     * @return void
     */
    public function injectPages( Model\Pages $pages )
    {
        $this->pages = $pages;
    }

    /**
     * Get injected pages model.
     *
     * @return Model\Pages
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Inject menu model.
     *
     * @param Model\Menu $menu
     * @return void
     */
    public function injectMenu( Model\Menu $menu )
    {
        $this->menu = $menu;
    }

    /**
     * Get injected menu model.
     *
     * @return Model\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Inject modules model.
     *
     * @param Model\Modules $modules
     * @return void
     */
    public function injectModules( Model\Modules $modules )
    {
        $this->modules = $modules;
    }

    /**
     * Get injected modules model.
     *
     * @return Model\Modules
     */
    public function getModules()
    {
        return $this->modules;
    }

    public function locale()
    {
        return $this->lang;
    }
    
    /**
     * Perform presenter startup actions.
     *
     * @return void
     */
    protected function startup(): void
    {
        parent::startup();
        
        // localization handling
        $session = $this->getSession()->getSection('lang');
        $lang = $this->getParameter('lang') ?? $session->lang ?? 'cz';
        $session->lang = $lang;
        $this->lang = $lang;

        $this->getUser()->setAuthenticator($this->getAuthenticator());

        /** @var Nette\Bridges\SecurityHttp\SessionStorage $storage */
        $storage = $this->getUser()->getStorage();
        $storage->setNamespace('frontend');

        $this->template->setTranslator($this->translator);
    }

    /**
     * Handle frontend sign-out and redirect to homepage.
     *
     * @return void
     */
    public function handleSignOut()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
            $this->flashMessage("Odhlášení proběhlo úspěšně.");
            $this->redirect("Homepage:default");
        }
    }

    /**
     * Create menu component.
     *
     * @return Components\MenuControl
     */
    protected function createComponentMenu(): Components\MenuControl
    {
        return $this->menuControl->create();
    }

    /**
     * Create CSS loader component for frontend assets.
     *
     * @param array|null $cssFiles
     * @return \WebLoader\Nette\CssLoader
     */
    protected function createComponentCss($cssFiles = null)
    {
        $wwwDir        = './www/frontend/css';
        $contentTmpDir = './www/frontend/cache';

        $files = new \WebLoader\FileCollection($wwwDir);

        $files->addFiles(array('header.css'));
        $files->addFiles(array('default.css'));

        $presenterName = explode(":", $this->getName())[1];
        $files->addFiles(array(strtolower($presenterName . '/' . $this->getAction() . '.css')));
        if (is_array($cssFiles !== null)) {
            $files->addFiles($cssFiles);
        }

        $compiler = \WebLoader\Compiler::createCssCompiler($files, $contentTmpDir);

        $compiler->addFilter(function ($code) {
            return \CssMin::minify($code);
        });

        $control = new \WebLoader\Nette\CssLoader($compiler, $this->getHttpRequest()->getUrl()->getBasePath() . 'www/frontend/cache');
        $control->setMedia('screen');

        return $control;
    }

    /**
     * Prepare template variables before render.
     *
     * @return void
     */
    public function beforeRender()
    {
        $this->template->netteUser = $this->getUser();
        $this->template->blogItems = $this->getBlog()->findAll($this->locale(), ["active" => 1], 'time DESC');
        $termsConditionRecord = $this->getTermsConditions()->find(1);
        $this->template->termsCondition = $termsConditionRecord;
        $this->template->termsConditionTime = $termsConditionRecord->data()->time_modify;
        $this->template->pdp = $this->getPersonalDataProtections()->find(1);
        $this->template->settingRecord = $this->getSettings()->find(1);
    }

    /**
     * Get the current Nette user instance.
     *
     * @return Nette\Security\User
     */
    public function getNetteUser()
    {
        return $this->getUser();
    }

    /**
     * Create sign-in form component.
     *
     * @return Form
     */
    public function createComponentSignInForm()
    {
        $form = new Form;
        $form->addText('username', 'E-mail')->setRequired('Prosím, vyplňte svůj e-mail.')->setHtmlAttribute("class", "uk-input");
        $form->addPassword('password', 'Heslo')->setRequired('Prosím, vyplňte své heslo.')->setHtmlAttribute("class", "uk-input");
        $form->addSubmit('submit', 'Přihlásit')->setHtmlAttribute("class", "button red red-shadow");
        $form->addHidden('redirect', $this->getParameter("u") );
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * Handle submission of the sign-in form.
     *
     * @param Form $form
     * @param \stdClass $values
     * @return void
     */
    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->getUser()->setExpiration(null);
            $this->flashMessage("Byl jste úspěšně přihlášen.");
            if ($values->redirect) {
                $this->redirectUrl(base64_decode($values->redirect));
            } else {
                $this->redirect('this');
            }
        } catch ( Nette\Security\AuthenticationException $e ) {
            bdump($e);
            $form->addError( $e->getMessage() );
        }
    }
}