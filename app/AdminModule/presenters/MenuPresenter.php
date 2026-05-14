<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing menu items in the admin panel.
 */
final class MenuPresenter extends AdminPresenter
{
	/** @var Model\Menu */
	private Model\Menu $menu;

	/**
	 * Constructor for MenuPresenter.
	 *
	 * @param Model\Menu $menu
	 */
	public function __construct( Model\Menu $menu )
	{
		parent::__construct();
		$this->menu = $menu;
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
	 * Default action to list menu items.
	 *
	 * @return void
	 */
	public function actionDefault()
	{
		$this->template->headerName = "Menu";
		$this->template->menuItems = $this->menu->findAll();
	}

	/**
	 * Get the record by ID from parameters.
	 *
	 * @return mixed
	 */
	public function getRecord() 
	{
		$id = $this->getParameter('id');
		$record = $this->menu->find($id);
		return $record;
	}

	/**
	 * Action to display detail of a menu item.
	 *
	 * @param int $id
	 * @return void
	 */
	public function actionDetail( $id ) 
	{
		$record = $this->menu->find($id);
		if (! $record) 
			$this->error("Položka nenalezena.");
		
		$this->template->record = $record;
	}

	/**
	 * Handle the removal of a menu item.
	 *
	 * @param int $id
	 * @return void
	 */
	public function handleRemove( $id )
	{
		$this->menu->remove($id);
		$this->flashMessage("Položka byla odstraněna.", 'danger');
		$this->redirect('this');
	}

	/**
	 * Create the form component for inserting a new menu item.
	 *
	 * @return Form
	 */
	public function createComponentInsertForm() 
	{
		$form = new Form;

		$form->addText("label", "Label")->setHtmlAttribute("class", "uk-input");
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
	public function insertFormSucceeded( Form $form, $values )
	{
		$time = time();

		$values["time_create"] = $time;
		$values["time_modify"] = $time;
		$values["create_user_id"] = $this->getAdminUser()->data()->id;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;

		$result = $this->menu->insert( $values );

		$this->redirect("Menu:detail", ['id' => $result]);
	}

	/**
	 * Create the form component for editing an existing menu item.
	 *
	 * @return Form
	 */
	public function createComponentEditForm() 
	{
		$form = new Form;

		$record = $this->getRecord();

		$form->addText( "label", "Label" )->setHtmlAttribute( "class", "uk-input" )->setDefaultValue($record->data()->label);

		$form->addSubmit( "submit", "Uložit" )->setHtmlAttribute( "class", "btn btn-primary uk-margin-top" );

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
	public function editFormSucceeded( Form $form, $values )
	{
		$time = time();
		
		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		$values["time_modify"] = $time;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;

		$record->update($values);
		$this->flashMessage("Položka byl uložena.");

		$this->redirect("this");
	}
}
