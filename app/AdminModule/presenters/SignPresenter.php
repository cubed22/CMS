<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use App\AdminModule\Authenticator\AdminAuthenticator;
use App\Model;

/**
 * Presenter for handling admin sign-in functionality.
 */
final class SignPresenter extends Nette\Application\UI\Presenter
{
    /** @var AdminAuthenticator Authenticator */
    private AdminAuthenticator $adminAuthenticator;

    /** @var Model\Users */
    private Model\Users $users;

    /** @var Model\Settings */
    private Model\Settings $settings;

    /**
     * Get the users model.
     *
     * @return Model\Users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Get the settings model.
     *
     * @return Model\Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
    * Constructor for SignPresenter.
    *
    * @param AdminAuthenticator $adminAuthenticator
    * @param Model\Users $users
    * @param Model\Settings $settings
    */
    public function __construct(AdminAuthenticator $adminAuthenticator, Model\Users $users, Model\Settings $settings)
    {
        parent::__construct();
        $this->adminAuthenticator = $adminAuthenticator;
        $this->users = $users;
        $this->settings = $settings;
        $this->setLayout(__DIR__ . '/../templates/@layout-login.latte');
    }

    /**
     * Startup method called before each action.
     */
    protected function startup(): void
    {
        parent::startup();
        $this->getUser()->setAuthenticator($this->adminAuthenticator);

        /** @var Nette\Bridges\SecurityHttp\SessionStorage $storage */
        $storage = $this->getUser()->getStorage();
        $storage->setNamespace('admin');
    }

    /**
     * Method called before rendering the template.
     */
    public function beforeRender(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $userId = $this->getUser()->getId();
            $user = $this->getUsers()->find($userId);
            if ($user !== false && $user->data()->role == 'admin') {
                $this->redirect( 'Homepage:default' );
            }
        }

        $this->template->settingRecord = $this->getSettings()->find(1);
    }

    /**
     * Create the sign-in form component.
     *
     * @return Form
     */
    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        
        $form->addText('email', 'E-mail:')->setRequired('Prosím vyplňte svůj e-mail.');
        $form->addPassword('password', 'Heslo:')->setRequired('Prosím vyplňte své heslo.');
        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful sign-in form submission.
     *
     * @param Form $form
     * @param \stdClass $values
     * @return void
     */
    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->login($values->email, $values->password);
            $this->getUser()->setExpiration(null);
            $this->flashMessage("Byl jste úspěšně přihlášen.");
            $this->redirect('Homepage:default');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }
}
