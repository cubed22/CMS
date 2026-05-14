<?php
namespace App\AdminModule\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model;
use Nette\Utils\Image;

final class SlideshowPresenter extends AdminPresenter
{
	private $slideshow;

	public function __construct( Model\Slideshow $slideshow )
	{
		parent::__construct();
		$this->slideshow = $slideshow;
	}

  public function startup(): void
  {
  	parent::startup();
  }

  public function renderDefault()
  {
  	$this->template->headerName = "Slideshow";
  	$this->template->slideshowItems = $this->slideshow->findAll();
  }

  public function getRecord() 
  {
  	$id = $this->getParameter('id');
  	$record = $this->slideshow->find($id);
  	return $record;
  }

  public function actionDetail( $id ) 
  {
  	$record = $this->slideshow->find($id);
  	if (! $record) 
  		$this->error("Položka nenalezena.");
  	
  	$this->template->record = $record;
  	$this->template->headerName = $record->data()->name == "" ? "Slideshow" : $record->data()->name;
  }

  public function handleRemove( $id )
  {
  	$record = $this->getRecord();
  	
  	if ( is_file( "www/upload/slideshow/" . $record->data()->filename ) ) {
			unlink( "www/upload/slideshow/" . $record->data()->filename );
		}
		if ( is_file( "www/upload/slideshow/small/" . $record->data()->filename ) ) {
			unlink( "www/upload/slideshow/small/" . $record->data()->filename );
		}

  	$this->slideshow->remove($id);
  	$this->flashMessage("Položka byla odstraněna.", 'danger');
  	$this->redirect( 'this' );
  }

	public function createComponentInsertForm() 
	{
		$form = new Form;

		$form->addUpload( "image", "Obrázek" )->setHtmlAttribute( "style", "width: 100%;" );
		$form->addSubmit( "submit", "Vložit" )->setHtmlAttribute( "class", "btn btn-primary uk-margin-top" );

		$form->onSuccess[] = [$this, 'insertFormSucceeded'];
		return $form;
	}

	public function insertFormSucceeded( Form $form, $values )
	{
		$file = $values['image'];

		if ( $file->isOk() ) {

			$image = $file->toImage();
			$filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
			$image->save( 'www/upload/slideshow/' . $filename, 80, Image::WEBP );
			$image->scale(250);
			$image->save( 'www/upload/slideshow/small/' . $filename, 80, Image::WEBP );

			$values['filename'] = $filename;
		}

		unset($values["image"]);

		$time = time();

		$values["time_create"] = $time;
		$values["time_modify"] = $time;
		$values["create_user_id"] = $this->getAdminUser()->data()->id;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;
		$result = $this->slideshow->insert( $values );

		$this->redirect("Slideshow:detail", ["id" => $result['id']]);
	}

	public function createComponentEditForm() {

		$form = new Form;

		$record = $this->getRecord();

		$form->addHidden("id");
		$form->addText( "name", "Název" )->setHtmlAttribute( "class", "uk-input" )->setDefaultValue($record->data()->name);
		$form->addText( "title", "Popisek" )->setHtmlAttribute( "class", "uk-input" )->setDefaultValue($record->data()->title);

		$form->addUpload( "image", "Obrázek" )->setHtmlAttribute( "style", "width: 100%;" );
		$form->addSubmit( "submit", "Uložit" )->setHtmlAttribute( "class", "btn btn-primary uk-margin-top" );

		$form->onSuccess[] = [$this, 'editFormSucceeded'];
		return $form;
	}

	public function editFormSucceeded( Form $form, $values )
	{
		$time = time();

		$record = $this->getRecord();
		if (! $record) 
			$this->error("Položka nenalezena.");

		$values["time_modify"] = $time;
		$values["modify_user_id"] = $this->getAdminUser()->data()->id;

		$file = $values['image'];

		if ( $file->isOk() ) {

			if ( is_file( "www/upload/slideshow/" . $record->data()->filename ) ) {
				unlink( "www/upload/slideshow/" . $record->data()->filename );
			}
			if ( is_file( "www/upload/slideshow/small/" . $record->data()->filename ) ) {
				unlink( "www/upload/slideshow/small/" . $record->data()->filename );
			}

			$image = $file->toImage();
			$filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
			$image->save( 'www/upload/slideshow/' . $filename, 80, Image::WEBP );
			$image->scale(250);
			$image->save( 'www/upload/slideshow/small/' . $filename, 80, Image::WEBP );

			$values['filename'] = $filename;
		}

		unset($values["image"]);

		$record->update($values);
		$this->flashMessage("Položka byla uložena.");

		$this->redirect("this");
	}
}
