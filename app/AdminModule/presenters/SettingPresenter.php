<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Presenter for managing general settings in the admin panel.
 */
final class SettingPresenter extends AdminPresenter
{
    /** @var Model\Settings */
    private Model\Settings $settings;

    /**
     * Constructor for SettingPresenter.
     *
     * @param Model\Settings $settings
     */
    public function __construct( Model\Settings $settings )
    {
      parent::__construct();
      $this->settings = $settings;
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
    * Default action to display settings.
    *
    * @return void
    */
    public function renderDefault()
    {
        $this->template->headerName = "Nastavení";
        $this->template->record = $this->getRecord();
    }

    /**
     * Get the record by ID from parameters.
     *
     * @return mixed
     */
    public function getRecord() 
    {
        $id = 1;
        $record = $this->settings->find($id);
        return $record;
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

        $form->addText("name", "Název webu")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->name);
        $form->addText("email_admin", "E-mail na administrátora")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->email_admin);
        $form->addUpload("image", "Logo")->setHtmlAttribute("style", "width: 100%;");

        $form->addText("facebook", "Facebook")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->facebook);
        $form->addText("instagram", "Instagram")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->instagram);
        $form->addText("twitter", "Twitter")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->twitter);
        $form->addText("tiktok", "TikTok")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->tiktok);

        $form->addSubmit( "submit", "Uložit" )->setHtmlAttribute( "class", "btn btn-primary uk-margin-top" );

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
            $this->error("Chyba.");

        $file = $values['image'];

        if ( $file->isOk() ) {

            if ( is_file( "www/upload/setting/logo/" . $record->data()->logo ) ) {
                unlink( "www/upload/setting/logo/" . $record->data()->logo );
            }
            if ( is_file( "www/upload/setting/logo/small/" . $record->data()->logo ) ) {
                unlink( "www/upload/setting/logo/small/" . $record->data()->logo );
            }

            $image = $file->toImage();
            $filename = date('YmdHis') . '-' . $file->name;
            $image->save( 'www/upload/setting/logo/' . $filename );
            $image->scale(250);
            $image->save( 'www/upload/setting/logo/small/' . $filename );

            $values['logo'] = $filename;
        }
        unset($values["image"]);

        $record->update($values);
        $this->flashMessage("Nastavení bylo uloženo.");

        $this->redirect("this");
    }

    /**
     * Create the edit payment gateway form component.
     * 
     * @return Form
     */
    public function createComponentEditPaymentGatewayForm() 
    {
        $form = new Form;

        $record = $this->getRecord();

        $form->addText("payment_gateway_merchant", "Merchant ID")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->payment_gateway_merchant);
        $form->addText("payment_gateway_secret", "Secret ID")->setHtmlAttribute("class", "uk-input")->setDefaultValue($record->data()->payment_gateway_secret);
        $form->addCheckbox("payment_gateway_test", "Testovací platební brána")->setHtmlAttribute("class", "uk-checkbox")->setDefaultValue($record->data()->payment_gateway_test);

        $form->addSubmit("submit", "Uložit")->setHtmlAttribute("class", "btn btn-primary uk-margin-top");

        $form->onSuccess[] = [$this, 'editPaymentGatewayFormSucceeded'];
        return $form;
    }

    /**
     * Handle successful submission of the edit payment gateway form.
     *
     * @param Form $form
     * @param array $values
     * @return void
     */
    public function editPaymentGatewayFormSucceeded( Form $form, $values )
    {
        $record = $this->getRecord();
        if (! $record) 
            $this->error("Chyba.");

        $record->update($values);
        $this->flashMessage("Nastavení platební brány bylo uloženo.");

        $this->redirect("this", ["tab" => "pay"]);
    }
}