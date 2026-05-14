<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing terms and conditions in the admin panel.
 */
final class TermsConditionPresenter extends AdminPresenter
{
	/** @var Model\Url */
	private Model\Url $url;

	/** @var Model\TermsConditions */
	private Model\TermsConditions $model;

	/**
	 * Constructor for TermsConditionPresenter.
	 *
	 * @param Model\Url $url
	 * @param Model\TermsConditions $model
	 */
	public function __construct( Model\Url $url, Model\TermsConditions $model)
	{
		parent::__construct();
		$this->model = $model;
		$this->url = $url;
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
	 * Get the record by ID from parameters.
	 *
	 * @return mixed
	 */
	public function getRecord() 
	{
		$record = $this->model->find(1);
		return $record;
	}

	/**
	 * Action to display detail of terms and conditions.
	 *
	 * @return void
	 */
	public function actionDetail() 
	{
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Nenalezeno.");
		
		$this->template->record = $record;
		$this->template->headerName = $record->data()->name;
	}

	/**
	 * Create the edit form component.
	 *
	 * @return Form
	 */
	public function createComponentEditForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText("name", "Název" )->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
		$form->addTextarea("content", "Obsah")->setHtmlAttribute("class", "uk-textarea editor")->setDefaultValue($record->data()->content);
		$form->addText("url", "Tvar URL")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->url);
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
		$time = time();
		
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Nenalezeno.");

		if ($values["url"] != $record->data()->url) {
			$values["url"] = $this->url->getUrl($values["url"]);
		}
		$values["time_modify"] = $time;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;

		$record->update($values);
		$this->flashMessage("Uloženo.");

		$this->redirect("this");
	}

	/**
	 * Create the edit SEO form component.
	 * 
	 * @return Form
	 */
	public function createComponentEditSeoForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText("title", "Titulek" )->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->title);
		$form->addText("description", "Popisek" )->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->description);
		$form->addText("keywords", "Klíčová slova" )->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->keywords);
		$form->addSubmit("submit", "Uložit" )->setHtmlAttribute("class", "btn btn-primary uk-margin-top" );

		$form->onSuccess[] = [$this, 'editSeoFormSucceeded'];
		return $form;
	}

	/**
	 * Handle successful submission of the edit SEO form.
	 *
	 * @param Form $form
	 * @param array $values
	 * @return void
	 */
	public function editSeoFormSucceeded(Form $form, $values)
	{
		$time = time();

		$record = $this->getRecord();
		if (! $record) 
			$this->error("Nenalezeno.");

		$values["time_modify"] = $time;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;

		$record->update($values);
		$this->flashMessage("SEO nastavení bylo uloženo.");

		$this->redirect("this");
	}
}
