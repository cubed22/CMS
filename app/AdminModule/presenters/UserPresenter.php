<?php
namespace App\AdminModule\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing users in the admin panel.
 */
final class UserPresenter extends AdminPresenter
{
	/** @var Model\Users */
	private Model\Users $users;

	/** @var Nette\Security\Passwords */
	private Nette\Security\Passwords $passwords;

	/**
	 * User presenter constructor.
	 *
	 * @param Model\Users $users
	 * @param Nette\Security\Passwords $passwords
	 */
	public function __construct( Model\Users $users, Nette\Security\Passwords $passwords )
	{
		parent::__construct();
		$this->users = $users;
		$this->passwords = $passwords;
	}

	/**
	 * Startup lifecycle hook.
	 *
	 * @return void
	 */
	public function startup(): void
	{
		parent::startup();
	}

	/**
	 * Default action to list users.
	 *
	 * @return void
	 */
	public function actionDefault()
	{
		$this->template->headerName = "Uživatelé";
		$this->template->users = $this->users->findAll(null, "id DESC");
	}

	/**
	 * Get the user record by ID from parameters.
	 *
	 * @return mixed
	 */
	public function getRecord() 
	{
		$id = $this->getParameter('id');
		$record = $this->users->find($id);
		return $record;
	}

	/**
	 * Action to display detail of a user.
	 *
	 * @param int $id
	 * @return void
	 */
	public function actionDetail( $id ) 
	{
		$record = $this->users->find($id);
		if (! $record) 
			$this->error("Položka nenalezena.");
		
		$this->template->record = $record;
		$this->template->headerName = $record->getFullName() . " - " . $record->data()->email;
	}

	/**
	 * Handle the removal of a user.
	 *
	 * @param int $id
	 * @return void
	 */
	public function handleRemove( $id )
	{
		$record = $this->getRecord();
		$tab = $record->data()->role;

		$this->users->remove($id);
		$this->flashMessage("Položka byla odstraněna.", 'danger');
		$this->redirect('this', ["tab" => $tab]);
	}

	/**
	 * Create the form component for inserting a new user.
	 *
	 * @return Form
	 */
	public function createComponentInsertForm() {

		$form = new Form;

		$form->addText("email", "E-mail")->setHtmlAttribute("class", "uk-input");
		$form->addSubmit("submit", "Vložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

		$form->onSuccess[] = [$this, 'insertFormSucceeded'];
		return $form;
	}

	/**
	 * Handle successful submission of the insert form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function insertFormSucceeded( Form $form, $values )
	{
		$result = $this->users->insert( $values );

		$this->redirect("User:detail", ["id" => $result['id']]);
	}

	/**
	 * Create the form component for editing an existing user.
	 *
	 * @return Form
	 */
	public function createComponentEditForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText("name", "Jméno")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
		$form->addText("surname", "Příjmení")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->surname);
		$form->addText("email", "E-mail / Login")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->email);
		$form->addText("new_password", "Nastavit heslo")->setHtmlAttribute("class", "uk-input");
		$form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

		$form->onSuccess[] = [$this, 'editFormSucceeded'];
		return $form;
	}

	/**
	 * Handle successful submission of the edit form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function editFormSucceeded( Form $form, $values )
	{
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		if ($values["new_password"] !== '') {
			$values["password"] = $this->passwords->hash($values["new_password"]);
		}
		unset($values["new_password"]);

		$record->update($values);
		$this->flashMessage("Položka byla uložena.");
		$this->redirect("this");
	}
}
