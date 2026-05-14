<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing cron jobs in the admin panel.
 */
final class CronPresenter extends AdminPresenter
{
	 /** @var Model\Cronmaster */
	private Model\Cronmaster $cron;

	/**
	 * Constructor for CronPresenter.
	 *
	 * @param Model\Cronmaster $cron
	 */
	public function __construct(Model\Cronmaster $cron)
	{
		parent::__construct();
		$this->cron = $cron;
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
	 * Default action to list cron jobs.
	 *
	 * @return void
	 */
	public function renderDefault()
	{
		$this->template->headerName = "Cronmaster";
		$this->template->cronItems = $this->cron->findAll(null, "id DESC");
	}

	/**
	 * Get the record by ID from parameters.
	 *
	 * @return mixed
	 */
	public function getRecord()
	{
		$id = $this->getParameter('id');
		$record = $this->cron->find($id);
		return $record;
	}

	/**
	 * Action to display detail of a cron job.
	 *
	 * @param int $id
	 * @return void
	 */
	public function actionDetail($id) 
	{
		$record = $this->cron->find($id);
		if (! $record) 
			$this->error("Položka nenalezena.");
		
		$this->template->record = $record;
	}

	/**
	 * Handle the removal of a cron job.
	 *
	 * @param int $id
	 * @return void
	 */
	public function handleRemove($id)
	{
		$this->cron->remove($id);
		$this->flashMessage("Položka byla odstraněna.", 'danger');
		$this->redirect('this');
	}

	/**
	 * Handle toggling the active state of a cron job.
	 *
	 * @param int $id
	 * @return void
	 */
	public function handleToggleActive($id)
	{
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		$record->update(["active" => !$record->data()->active]);

		$this->flashMessage('Položka byla upravena.');
		$this->redrawControl('messageSnippet');
		$this->redrawControl('itemsSnippet');
	}

	/**
	 * Create the form component for inserting a new cron job.
	 * 
	 * @return Form
	 */
	public function createComponentInsertForm() 
	{
		$form = new Form;

		$form->addText("name", "Název")->setHtmlAttribute("class", "uk-input")->setRequired("Prosím, vyplňte název");
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
		$values["active"] = 0;
		$values["method"] = "";
		$values["hour"] = "";
		$values["minute"] = "";
		$values["last_exec"] = null;
		$values["next_exec"] = null;
		$result = $this->cron->insert($values);

		$this->redirect("Cron:detail", ["id" => $result['id']]);
	}

	/**
	 * Create the form component for editing an existing cron job.
	 *
	 * @return Form
	 */
	public function createComponentEditForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText("name", "Název")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
		$form->addText("method", "Metoda")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->method);
		$form->addText("hour", "Hodina spuštění")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->hour);
		$form->addText("minute", "Minuta spuštění")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->minute);
		
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
	public function editFormSucceeded(Form $form, $values)
	{
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		$nextExec = strtotime('tomorrow ' . $values["hour"] . ':' . $values["minute"]);
		$values["next_exec"] = $nextExec;

		$record->update($values);
		$this->flashMessage("Položka byla uložena.");

		$this->redirect("this");
	}
}
