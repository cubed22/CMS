<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing messages in the admin panel.
 */
final class MessagePresenter extends AdminPresenter
{
	/** @var Model\Mailer */
	private Model\Mailer $mailer;

	/** @var Model\Messages */
	private Model\Messages $messages;

	/** @var Model\LogMessages */
	private Model\LogMessages $logMessages;

	/**
	 * Constructor for MessagePresenter.
	 *
	 * @param Model\Mailer $mailer
	 * @param Model\Messages $messages
	 * @param Model\LogMessages $logMessages
	 */
	public function __construct( Model\Mailer $mailer, Model\Messages $messages, Model\LogMessages $logMessages )
	{
		parent::__construct();
		$this->mailer = $mailer;
		$this->messages = $messages;
		$this->logMessages = $logMessages;
	}

	/**
	 * Startup method called before each action.
	 *
	 * @return void
	 */
	public function startup(): void
	{
		parent::startup();
	}

	/**
	 * Default action to list messages and sent emails.
	 *
	 * @return void
	 */
	public function renderDefault()
	{
		$this->template->headerName = "E-maily";
	}

	/**
	 * Get the record by ID from parameters.
	 *
	 * @return mixed
	 */
	public function getRecord() 
	{
		$id = $this->getParameter('id');
		$record = $this->messages->find($id);
		return $record;
	}

	/**
	 * Action to display detail of a message.
	 *
	 * @param int $id
	 * @return void
	 */
	public function actionDetail( $id ) 
	{
		$record = $this->messages->find($id);
		if (! $record) 
			$this->error("Položka nenalezena.");
		
		$this->template->record = $record;
		$this->template->logMessages = $record->messages(null, "id DESC");
	}

	/**
	 * Default action to list messages and sent emails.
	 *
	 * @return void
	 */
	public function actionDefault()
	{
		$this->template->messageItems = $this->messages->findAll(null, "id DESC");
		$this->template->sentEmailsItems = $this->logMessages->findAll(null, "time DESC", 1000);
	}

	/**
	 * Handle the removal of a message.
	 *
	 * @param int $id
	 * @return void
	 */
	public function handleRemove( $id )
	{
		$this->messages->remove($id);
		$this->flashMessage("Položka byla odstraněna.", 'danger');
		$this->redirect('this');
	}

	/**
	 * Create the form component for inserting a new message.
	 *
	 * @return Form
	 */
	public function createComponentInsertForm() 
	{
		$form = new Form;

		$form->addText("subject", "Název")->setHtmlAttribute("class", "uk-input")->setRequired("Prosím, vyplňte název");
		$form->addSubmit("submit", "Vložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

		$form->onSuccess[] = [$this, 'insertFormSucceeded'];
		return $form;
	}

	/**
	 * Handle the successful submission of the insert form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function insertFormSucceeded(Form $form, $values)
	{
		$result = $this->messages->insert($values);

		$this->redirect("Message:detail", ["id" => $result['id']]);
	}

	/**
	 * Create the form component for editing an existing message.
	 *
	 * @return Form
	 */
	public function createComponentEditForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText("name", "Interní název")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
		$form->addText("subject", "Předmět")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->subject);
		$form->addTextarea("body", "Obsah zprávy")->setHtmlAttribute("class", "uk-textarea editor")->setDefaultValue($record->data()->body)->setAttribute('rows', 40);
		
		$form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

		$form->onSuccess[] = [$this, 'editFormSucceeded'];
		return $form;
	}

	/**
	 * Handle the successful submission of the edit form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function editFormSucceeded(Form $form, $values)
	{
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		$record->update($values);
		$this->flashMessage("Položka byla uložena.");

		$this->redirect("this");
	}

	public function createComponentSendRecipientEmailForm() 
	{
		$form = new Form;

		$form->addText("recipient", "Adresáti (odděleni středníkem)")->setHtmlAttribute("class", "uk-input")->setRequired("Prosím, vyplňte alespoň jednoho adresáta");
		$form->addText("subject", "Předmět")->setHtmlAttribute("class", "uk-input")->setRequired("Prosím, vyplňte předmět");
		$form->addTextarea("body", "Obsah zprávy")->setHtmlAttribute("class", "uk-textarea editor")->setHtmlAttribute('rows', 40)->setRequired("Prosím, vyplňte obsah zprávy");
		
		$form->addSubmit( "submit", "Odeslat zprávu" )->setHtmlAttribute( "class", "btn btn-primary uk-margin-top" );

		$form->onSuccess[] = [$this, 'sendRecipientEmailFormSucceeded'];
		return $form;
	}

	/**
	 * Handle the successful submission of the send recipient email form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function sendRecipientEmailFormSucceeded(Form $form, $values)
	{
		if ($values["recipient"] != "") {
			$emailList = explode(";", $values["recipient"]);
			$subject = $values["subject"];
			$body = $values["body"];
			$this->mailer->sendMessageCustomTemplate($emailList, $subject, $body);

			$this->flashMessage("E-maily byly odeslány.");
		} else {
			$this->flashMessage("Seznam adres je prázdný.", "danger");
		}

		$this->redirect("this", ["tab" => "kon"]);
	}
}
