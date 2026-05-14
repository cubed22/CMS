<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing blog categories in the admin panel.
 */
final class BlogCategoryPresenter extends AdminPresenter
{
	/** @var Model\Url */
    private Model\Url $url;

	/** @var Model\BlogCategories */
    private Model\BlogCategories $blogCategories;

    /**
     * Constructor for BlogCategoryPresenter.
     *
     * @param Model\BlogCategories $blogCategories
     * @param Model\Url $url
     */
    public function __construct(Model\BlogCategories $blogCategories, Model\Url $url)
    {
        parent::__construct();
        $this->blogCategories = $blogCategories;
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
     * Default action to list blog categories.
     *
     * @return void
     */
    public function actionDefault()
    {
      $this->template->headerName = "Kategorie článků";
      $this->template->blogCategoryItems = $this->blogCategories->findAll(null, "time_create DESC");
    }

    /**
     * Get the record by ID from parameters.
     *
     * @return mixed
     */
    public function getRecord()
    {
        $id = $this->getParameter('id');
        $record = $this->blogCategories->find($id);
        return $record;
      }

    /**
     * Action to display detail of a blog category.
     *
     * @param int $id
     * @return void
     */
    public function actionDetail($id)
    {
        $record = $this->blogCategories->find($id);
        if (!$record) {
            $this->error("Kategorie článků nenalezena.");
        }

        $this->template->headerName = $record->data()->name;
        $this->template->record = $record;
      }

    /**
     * Handle removal of a blog category.
     *
     * @param int $id
     * @return void
     */
    public function handleRemove($id)
    {
        $record = $this->getRecord();

        if (is_file("www/upload/blogCategory/" . $record->data()->filename)) {
            unlink("www/upload/blogCategory/" . $record->data()->filename);
        }
        if (is_file("www/upload/blogCategory/small/" . $record->data()->filename)) {
            unlink("www/upload/blogCategory/small/" . $record->data()->filename);
        }

        $this->blogCategories->remove($id);
        $this->flashMessage("Kategorie byla odstraněna.", 'danger');
        $this->redirect('this');
      }

    /**
     * Create the insert form component.
     *
     * @return Form
     */
    public function createComponentInsertForm()
    {
        $form = new Form;

        $form->addText("name", "Název")->setHtmlAttribute("class", "uk-input");
        $form->addSubmit("submit", "Vložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful insert form submission.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function insertFormSucceeded(Form $form, $values)
    {
        $time = time();

        $values["url"] = $this->url->getUrl($values["name"]);
        $values["time_create"] = $time;
        $values["time_modify"] = $time;
        $values["create_user_id"] = $this->getAdminUser()->data()->id;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $result = $this->blogCategories->insert($values);

        $this->redirect("BlogCategory:detail", ["id" => $result['id']]);
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
        $form->addText("name", "Název")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
        $form->addTextarea("content", "Obsah")->setHtmlAttribute("class", "uk-textarea")->setDefaultValue($record->data()->content);
        $form->addText("url", "Tvar URL")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->url);
        $form->addUpload("image", "Obrázek")->setHtmlAttribute("style", "width: 100%;");
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful edit form submission.
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
            $this->error("Kategorie nenalezena.");

        if ($values["url"] != $record->data()->url) {
            $values["url"] = $this->url->getUrl($values["url"]);
        }
        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $file = $values['image'];

        if ( $file->isOk() ) {
            $image = $file->toImage();
            $filename = date('YmdHis') . '-' . $file->name;
            $image->save( 'www/upload/blogCategory/' . $filename );
            $image->scale(250);
            $image->save( 'www/upload/blogCategory/small/' . $filename );

            $values['filename'] = $filename;
        }

        unset($values["image"]);

        $record->update($values);
        $this->flashMessage("Kategorie byla uložena.");

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
        $form->addText("title", "Titulek")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->title);
        $form->addText("description", "Popisek")->setHtmlAttribute("class", "uk-input ")->setDefaultValue($record->data()->description);
        $form->addText("keywords", "Klíčová slova")->setHtmlAttribute("class", "uk-input ")->setDefaultValue($record->data()->keywords);
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editSeoFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful edit SEO form submission.
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
            $this->error("Kategorie nenalezena.");

        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $record->update($values);
        $this->flashMessage("SEO nastavení bylo uloženo.");

        $this->redirect("this", ["tab" => "seo"]);
    }

    /**
     * Handle removal of the image from the blog category.
     *
     * @return void
     */
    public function handleRemoveImage()
    {
        $record = $this->getRecord();

        if ( $record ) 
        {
            if ( is_file( "www/upload/blogCategory/" . $record->data()->filename ) ) {
                unlink( "www/upload/blogCategory/" . $record->data()->filename );
            }
            if ( is_file( "www/upload/blogCategory/small/" . $record->data()->filename ) ) {
                unlink( "www/upload/blogCategory/small/" . $record->data()->filename );
            }

            $values['filename'] = null;
            $record->update($values);

            $this->flashMessage( 'Obrázek byl odstraněn.' );
            $this->redirect( 'this' );
        } else {
            $this->flashMessage( 'Článek nebyl nalezen.' );
        }
    }
}

