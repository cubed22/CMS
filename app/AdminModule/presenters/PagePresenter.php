<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;
use Nette\Utils\Image;

/**
 * Presenter for managing pages in the admin panel.
 */
final class PagePresenter extends AdminPresenter
{
    /** @var Model\Url */
    private $url;
    
    /** @var Model\Pages */
    private $pages;

    /**
     * Constructor for PagePresenter.
     *
     * @param Model\Url $url
     * @param Model\Pages $pages
     */
    public function __construct(Model\Url $url, Model\Pages $pages)
    {
        parent::__construct();
        $this->url = $url;
        $this->pages = $pages;
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
     * Render default page list view.
     *
     * @return void
     */
    public function renderDefault()
    {
        $this->template->headerName = "Stránky";
        $this->template->pageItems = $this->pages->findAll(null, "time_create DESC");
    }

    /**
     * Get the current page record from parameters.
     *
     * @return mixed
     */
    public function getRecord()
    {
        $id = $this->getParameter('id');
        $record = $this->pages->find($id);
        return $record;
    }

    /**
     * Action to display page details.
     *
     * @param int $id
     * @return void
     */
    public function actionDetail($id)
    {
        $record = $this->pages->find($id);
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        $this->template->record = $record;
        $this->template->headerName = $record->data()->name;
        $this->template->imageItems = $record->images();
    }

    /**
     * Handle page removal.
     *
     * @param int $id
     * @return void
     */
    public function handleRemove($id)
    {
        $record = $this->getRecord();

        if (is_file("www/upload/page/" . $record->data()->filename)) {
            unlink("www/upload/page/" . $record->data()->filename);
        }
        if (is_file("www/upload/page/small/" . $record->data()->filename)) {
            unlink("www/upload/page/small/" . $record->data()->filename);
        }
        if (is_file("www/upload/page/exact/" . $record->data()->filename)) {
            unlink("www/upload/page/exact/" . $record->data()->filename);
        }

        foreach ($record->images() as $image) {
            if (is_file("www/upload/page/images/" . $image->data()->filename)) {
                unlink("www/upload/page/images/" . $image->data()->filename);
            }
            if (is_file("www/upload/page/images/small/" . $image->data()->filename)) {
                unlink("www/upload/page/images/small/" . $image->data()->filename);
            }
        }

        $this->pages->remove($id);
        $this->flashMessage("Položka byl odstraněna.", 'danger');
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
     * Handle insert form success.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function insertFormSucceeded(Form $form, $values)
    {
        $values["url"] = $this->url->getUrl($values["name"]);
        $time = time();
        $values["time_create"] = $time;
        $values["time_modify"] = $time;
        $values["create_user_id"] = $this->getAdminUser()->data()->id;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;
        $result = $this->pages->insert($values);

        $this->redirect("Page:detail", ["id" => $result['id']]);
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
        $form->addCheckbox("active", "Zveřejněný?")->setHtmlAttribute("class", "uk-checkbox")->setDefaultValue($record->data()->active);
        $form->addTextarea("content", "Obsah")->setHtmlAttribute("class", "uk-textarea editor")->setDefaultValue($record->data()->content)->setAttribute('rows', 40);
        $form->addText("url", "Tvar URL")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->url);
        $form->addUpload("image", "Obrázek")->setHtmlAttribute("style", "width: 100%;");
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    /**
     * Handle edit form success.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function editFormSucceeded(Form $form, $values)
    {
        $time = time();

        $record = $this->getRecord();
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        if ($values["url"] != $record->data()->url) {
            $values["url"] = $this->url->getUrl($values["url"]);
        }
        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $file = $values['image'];

        if ($file->isOk()) {
            if (is_file("www/upload/page/" . $record->data()->filename)) {
                unlink("www/upload/page/" . $record->data()->filename);
            }
            if (is_file("www/upload/page/small/" . $record->data()->filename)) {
                unlink("www/upload/page/small/" . $record->data()->filename);
            }
            if (is_file("www/upload/page/exact/" . $record->data()->filename)) {
                unlink("www/upload/page/exact/" . $record->data()->filename);
            }

            $image = $file->toImage();
            $filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
            $image->save('www/upload/page/' . $filename, 80, Image::WEBP);
            $image->resize(320, 320, Image::FILL);
            $image->save('www/upload/page/exact/' . $filename, 80, Image::WEBP);
            $image->scale(250);
            $image->save('www/upload/page/small/' . $filename);

            $values['filename'] = $filename;
        }

        unset($values["image"]);

        $record->update($values);
        $this->flashMessage("Položka byla uložena.");

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
        $form->addText("description", "Popisek")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->description);
        $form->addText("keywords", "Klíčová slova")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->keywords);
        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editSeoFormSucceeded'];
        return $form;
    }

    /**
     * Handle edit SEO form success.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function editSeoFormSucceeded(Form $form, $values)
    {
        $time = time();

        $record = $this->getRecord();
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        $values["time_modify"] = $time;
        $values["modify_user_id"] = $this->getAdminUser()->data()->id;

        $record->update($values);
        $this->flashMessage("SEO nastavení bylo uloženo.");

        $this->redirect("Page:detail", ["id" => $record->data()->id, 'tab' => 'seo']);
    }

    /**
     * Handle removal of the front image.
     *
     * @return void
     */
    public function handleRemoveFrontImage()
    {
        $record = $this->getRecord();

        if ($record) {
            if (is_file("www/upload/page/" . $record->data()->filename)) {
                unlink("www/upload/page/" . $record->data()->filename);
            }
            if (is_file("www/upload/page/small/" . $record->data()->filename)) {
                unlink("www/upload/page/small/" . $record->data()->filename);
            }
            if (is_file("www/upload/page/exact/" . $record->data()->filename)) {
                unlink("www/upload/page/exact/" . $record->data()->filename);
            }

            $values['filename'] = null;
            $record->update($values);

            $this->flashMessage('Obrázek byl odstraněn.');
            $this->redirect('this');
        } else {
            $this->flashMessage('Položka nebyla nalezena.');
        }
    }

    /**
     * Handle removal of a page image.
     *
     * @param int $imageId
     * @return void
     */
    public function handleRemoveImage($imageId)
    {
        $record = $this->getRecord();
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        $imageRecord = $this->pages->findImage($imageId);

        if ($imageRecord) {
            if (is_file("www/upload/page/images/" . $imageRecord->data()->filename)) {
                unlink("www/upload/page/images/" . $imageRecord->data()->filename);
            }
            if (is_file("www/upload/page/images/small/" . $imageRecord->data()->filename)) {
                unlink("www/upload/page/images/small/" . $imageRecord->data()->filename);
            }

            $this->pages->removeImage($imageId);
            $this->flashMessage('Obrázek byl odstraněn.');
        } else {
            $this->flashMessage('Obrázek nebyl nalezen.');
        }

        $this->redirect("Page:detail", ["id" => $record->data()->id, 'tab' => 'img']);
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
     * Handle insert image form success.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function insertImageFormSucceeded(Form $form, $values)
    {
        $record = $this->getRecord();
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        foreach ($values['filename'] as $file) {
            if ($file->isOk()) {
                $image = $file->toImage();
                $filename = date('YmdHis') . '-' . pathinfo($file->name, PATHINFO_FILENAME) . '.webp';
                $image->save('www/upload/page/images/' . $filename, 80, Image::WEBP);
                $image->scale(750);
                $image->save('www/upload/page/images/small/' . $filename, 80, Image::WEBP);
                $vals['filename'] = $filename;
            }

            $vals['title'] = '';
            $vals['page_id'] = $record->data()->id;
            $this->pages->insertImage($vals);
        }

        $this->redirect("Pages:detail", ["id" => $record->data()->id, 'tab' => 'img']);
    }

    /**
     * Toggle active status for a page.
     *
     * @param int $id
     * @return void
     */
    public function handleToggleActive($id)
    {
        $record = $this->getRecord();
        if (!$record) {
            $this->error("Položka nenalezena.");
        }

        $record->update(["active" => !$record->data()->active]);

        $this->flashMessage('Položka byla upravena.');

        $this->redrawControl('messageSnippet');
        $this->redrawControl('itemsSnippet');
    }
}

