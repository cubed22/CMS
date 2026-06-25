<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use App\Model;
use Nette\Utils\Image;

/**
 * Presenter for managing blog items in the admin panel.
 */
final class BlogPresenter extends AdminPresenter
{
    /** @var Model\Url */
    private Model\Url $url;

    /** @var Model\Blog */
    private Model\Blog $blog;

    /** @var Model\BlogCategories */
    private Model\BlogCategories $blogCategories;

    /**
     * Constructor for BlogPresenter.
     *
     * @param Model\Url $url
     * @param Model\Blog $blog
     * @param Model\BlogCategories $blogCategories
     */
    public function __construct(Model\Url $url, Model\Blog $blog, Model\BlogCategories $blogCategories)
    {
        parent::__construct();
        $this->url = $url;
        $this->blog = $blog;
        $this->blogCategories = $blogCategories;
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
     * Render default view for listing blog items.
     *
     * @return void
     */
    public function renderDefault()
    {
        $this->template->headerName = "Blog";
        $this->template->blogItems = $this->blog->findAll($this->locale(), null, "time DESC");
    }

    /**
     * Get the record by ID from parameters.
     *
     * @return mixed
     */
    public function getRecord()
    {
        $id = $this->getParameter('id');
        $record = $this->blog->find($id, $this->locale());
        return $record;
    }

    /**
     * Action to display detail of a blog item.
     *
     * @param int $id
     * @return void
     */
    public function actionDetail($id)
    {
        $record = $this->blog->find($id, $this->locale());
        if (!$record)
            $this->error("Článek nenalezen.");

        $this->template->record = $record;
        $this->template->headerName = $record->locale()->name;

        $this->template->imageItems = $record->images();
    }

    /**
     * Handle removal of a blog item.
     *
     * @param int $id
     * @return void
     */
    public function handleRemove($id)
    {
        $record = $this->getRecord();

        if (is_file("www/upload/blog/" . $record->data()->filename)) {
            unlink("www/upload/blog/" . $record->data()->filename);
        }
        if (is_file("www/upload/blog/small/" . $record->data()->filename)) {
            unlink("www/upload/blog/small/" . $record->data()->filename);
        }
        if (is_file("www/upload/blog/exact/" . $record->data()->filename)) {
            unlink("www/upload/blog/exact/" . $record->data()->filename);
        }

        foreach ($record->images() as $image) {
            if (is_file("www/upload/blog/images/" . $image->data()->filename)) {
                unlink("www/upload/blog/images/" . $image->data()->filename);
            }
            if (is_file("www/upload/blog/images/small/" . $image->data()->filename)) {
                unlink("www/upload/blog/images/small/" . $image->data()->filename);
            }
        }

        $this->blog->remove($id);
        $this->flashMessage("Článek byl odstraněn.", 'danger');
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

        $form->addText("name", "Název")->setHtmlAttribute("class", "uk-input")->setRequired("Prosím, vyplňte název");
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
        $values["url"] = $this->url->getUrl($values["name"]);
        $time = time();
        $values["time"] = $time;
        $values["time_create"] = $time;
        $values["time_modify"] = $time;
        $values["create_user_id"] = $this->getAdminUser()->data()->id;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;
        $result = $this->blog->insert($values);

        $this->redirect("Blog:detail", ["id" => $result['id']]);
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
        $form->addText("time", "Datum")->setHtmlAttribute("class", "uk-input flatpickr-date")->setDefaultValue(date("Y-m-d H:i", $record->data()->time));

        $form->addCheckbox("active", "Zveřejněný?")->setHtmlAttribute("class", "uk-checkbox")->setDefaultValue($record->data()->active);

        $blogCategories = $this->blogCategories->findAll();
        $items = [null => '==='];
        foreach ($blogCategories as $blogCategory) {
            $items[$blogCategory->data()->id] = $blogCategory->data()->name;
        }
        $selectedItems = [];
        foreach ($record->categories() as $category) {
            $selectedItems[$category->data()->id] = $category->data()->id;
        }
        $form->addMultiSelect("categories", "Kategorie/Štítky", $items)->setHtmlAttribute("class", "uk-select select2-multiple")->setDefaultValue($selectedItems);

        foreach ($this->getAllLanguages() as $lang) {
            $shortCut = $lang->data()->shortcut;
            $form->addText($shortCut . "_name", "Název")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->locale($shortCut)->name);
            $form->addTextarea($shortCut . "_content", "Obsah")->setHtmlAttribute("class", "uk-textarea editor")->setDefaultValue($record->locale($shortCut)->content)->setHtmlAttribute('rows', 40);
            $form->addText($shortCut . "_url", "Tvar URL")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->locale($shortCut)->url);
        }

        $form->addUpload("image", "Obrázek")->setHtmlAttribute("style", "width: 100%;");
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful edit form submission.
     *
     * @param Form $form
     * @param \Nette\Utils\ArrayHash $values
     * @return void
     */
    public function editFormSucceeded(Form $form, \Nette\Utils\ArrayHash $values)
    {
        $time = time();

        $record = $this->getRecord();
        if (! $record) 
            $this->error("Článek nenalezen.");

        $locals = $this->parseLocalizedValues($values);
        $this->verifyUrl($record, $locals);

        if ($values["time"] == "") {
            $values["time"] = null;
        }

        $array = [];
        foreach ($record->categories() as $recordCategory) {
            $array[] = $recordCategory->data()->id;
        }
        $catToCompare = array_merge(array_diff($array, $values["categories"]), array_diff($values["categories"], $array));
        foreach ($catToCompare as $catId) {
            if ($record->isInCategory($catId)) {
                $this->blog->removeFromCategory($record->data()->id, $catId);
            } else {
                $this->blog->addToCategory($record->data()->id, $catId);
            }
        }

        unset($values["categories"]);
        
        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $file = $values['image'];

        if ( $file->isOk() ) {

            if ( is_file( "www/upload/blog/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/" . $record->data()->filename );
            }
            if ( is_file( "www/upload/blog/small/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/small/" . $record->data()->filename );
            }
            if ( is_file( "www/upload/blog/exact/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/exact/" . $record->data()->filename );
            }

            $image = $file->toImage();
            $filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
            $image->save( 'www/upload/blog/' . $filename, 80, Image::WEBP );
            $image->resize(320, 320, Image::FILL);
            $image->save( 'www/upload/blog/exact/' . $filename, 80, Image::WEBP );
            $image->scale(250);
            $image->save( 'www/upload/blog/small/' . $filename, 80, Image::WEBP );

            $values['filename'] = $filename;
        }

        unset($values["image"]);

        $record->update($values, $locals);
        $this->flashMessage("Článek byl uložen.");

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

        foreach ($this->getAllLanguages() as $lang) {
            $shortCut = $lang->data()->shortcut;
            $form->addText($shortCut . "_title", "Titulek")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->locale($shortCut)->title);
            $form->addTextarea($shortCut . "_description", "Popisek")->setHtmlAttribute("class", "uk-textarea editor")->setDefaultValue($record->locale($shortCut)->description)->setHtmlAttribute('rows', 40);
            $form->addText($shortCut . "_keywords", "Klíčová slova")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->locale($shortCut)->keywords);
        }
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editSeoFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful edit SEO form submission.
     *
     * @param Form $form
     * @param \Nette\Utils\ArrayHash $values
     * @return void
     */
    public function editSeoFormSucceeded(Form $form, \Nette\Utils\ArrayHash $values)
    {
        $time = time();

        $record = $this->getRecord();
        if (! $record) 
            $this->error("Článek nenalezen.");

        $locals = $this->parseLocalizedValues($values);

        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $record->update($values, $locals);
        $this->flashMessage("SEO nastavení bylo uloženo.");

        $this->redirect("Blog:detail", ["id" => $record->data()->id, 'tab' => 'seo']);
    }

    /**
     * Handle removal of the front image.
     *
     * @return void
     */
    public function handleRemoveFrontImage()
    {
        $record = $this->getRecord();

        if ( $record ) 
        {
            if ( is_file( "www/upload/blog/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/" . $record->data()->filename );
            }
            if ( is_file( "www/upload/blog/small/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/small/" . $record->data()->filename );
            }
            if ( is_file( "www/upload/blog/exact/" . $record->data()->filename ) ) {
                unlink( "www/upload/blog/exact/" . $record->data()->filename );
            }

            $values['filename'] = null;
            $record->update($values);

            $this->flashMessage( 'Obrázek byl odstraněn.' );
            $this->redirect( 'this' );
        } else {
            $this->flashMessage( 'Článek nebyl nalezen.' );
        }
    }

    /**
     * Handle removal of an image.
     *
     * @param int $imageId
     * @return void
     */
    public function handleRemoveImage($imageId)
    {
        $record = $this->getRecord();
        if (! $record) 
            $this->error("Položka nenalezena.");

        $imageRecord = $this->blog->findImage($imageId);

        if ( $imageRecord ) 
        {
            if ( is_file( "www/upload/blog/images/" . $imageRecord->data()->filename ) ) {
                unlink( "www/upload/blog/images/" . $imageRecord->data()->filename );
            }
            if ( is_file( "www/upload/blog/images/small/" . $imageRecord->data()->filename ) ) {
                unlink( "www/upload/blog/images/small/" . $imageRecord->data()->filename );
            }

            $this->blog->removeImage($imageId);
            $this->flashMessage( 'Obrázek byl odstraněn.' );
        } else {
            $this->flashMessage( 'Obrázek nebyl nalezen.' );
        }

        $this->redirect("Blog:detail", ["id" => $record->data()->id, 'tab' => 'img']);
    }

    /**
     * Create the insert image form component.
     *
     * @return Form
     */
    public function createComponentInsertImageForm()
    {
        $form = new Form;

        $form->addMultiUpload("filename", "Obrázek")->setHtmlAttribute("style", "width: 100%;");
        $form->addSubmit("submit", "Vložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'insertImageFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful insert image form submission.
     *
     * @param Form $form
     * @param \Nette\Utils\ArrayHash $values
     * @return void
     */
    public function insertImageFormSucceeded(Form $form, \Nette\Utils\ArrayHash $values)
    {
        $record = $this->getRecord();
        if (! $record) 
            $this->error("Položka nenalezena.");

        foreach ($values['filename'] as $file) {
            if ( $file->isOk() ) {
                $image = $file->toImage();
                $filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
                $image->save( 'www/upload/blog/images/' . $filename, 80, Image::WEBP );
                $image->scale(750);
                $image->save( 'www/upload/blog/images/small/' . $filename, 80, Image::WEBP );
                $vals['filename'] = $filename;
            }

            $vals['title'] = '';
            $vals['blog_id'] = $record->data()->id;
            $this->blog->insertImage($vals);
        }

        $this->redirect("Blog:detail", ["id" => $record->data()->id, 'tab' => 'img']);
    }

    /**
     * Handle toggling the active status of a blog item.
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
}

