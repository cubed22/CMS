<?php

namespace App\AdminModule\Translator;

use Nette;

/**
 * AdminTranslator is a simple implementation of Nette\Localization\Translator for the admin panel.
 * It provides basic translation functionality based on predefined word forms.
 */
class AdminTranslator implements Nette\Localization\ITranslator
{

  use \Nette\SmartObject;

  public function __construct( )
  {

  }

  /**
   * Define the words and their forms for translation.
   *
   * @return array
   */
  private function words() 
  {
    $words = [];

    $words["ublaboo_datagrid.action"] = [ "Akce", "Akce", "Akce"];
    $words["ublaboo_datagrid.reset_filter"] = [ "Resetovat filtr", "resetovat filtr", "resetovat filtr" ];
    $words["ublaboo_datagrid.no_item_found_reset"] = [ "Žádné položky nenalezeny. Filtr můžete vynulovat", "Žádné položky nenalezeny. Filtr můžete vynulovat", "Žádné položky nenalezeny. Filtr můžete vynulovat" ];
    $words["ublaboo_datagrid.no_item_found"] = [ "Žádné položky nenalezeny.", "Žádné položky nenalezeny.", "Žádné položky nenalezeny." ];
    $words["ublaboo_datagrid.here"] = [ "zde", "zde", "zde" ];
    $words["ublaboo_datagrid.items"] = [ "Položky", "Položky", "Položky" ];
    $words["ublaboo_datagrid.all"] = [ "všechny", "všechny", "všechny" ];
    $words["ublaboo_datagrid.from"] = [ "z", "z", "z" ];
    $words["ublaboo_datagrid.reset_filter"] = [ "Resetovat filtr", "resetovat filtr", "resetovat filtr" ];
    $words["ublaboo_datagrid.group_actions"] = [ "Hromadné akce", "hromadné akce", "hromadné akce" ];
    $words["ublaboo_datagrid.show_all_columns"] = [ "Zobrazit všechny sloupce", "zobrazit všechny sloupce", "zobrazit všechny sloupce" ];
    $words["ublaboo_datagrid.hide_column"] = [ "Skrýt sloupec", "skrýt sloupec", "skrýt sloupec" ];
    $words["ublaboo_datagrid.action"] = [ "Akce", "akce", "akce" ];
    $words["ublaboo_datagrid.previous"] = [ "Předchozí", "předchozí", "předchozí" ];
    $words["ublaboo_datagrid.next"] = [ "Další", "další", "další" ];
    $words["ublaboo_datagrid.choose"] = [ "Vyberte", "vyberte", "vyberte" ];
    $words["ublaboo_datagrid.execute"] = [ "Provést", "provést", "provést" ];
    $words["ublaboo_datagrid.per_page_submit"] = [ "Potvrdit", "Potvrdit", "Potvrdit" ];

    return $words;
  }

  /**
   * Translate a message based on the count of items.
   *
   * @param string $message The message to translate.
   * @param mixed ...$parameters Additional parameters, where the first parameter is expected to be the count.
   * @return string The translated message.
   */
  public function translate($message, ...$parameters): string
  {
    $count = 1;
    $words = $this->words();

    if (isset($parameters[0])) $count = $parameters[0]; 

    if (isset($words[$message])) {
      if ($count == 1) {
        return $words[$message][0];
      } else if ($count == 2 || $count == 3 || $count == 4) {
        return $words[$message][1];
      } else {
        return $words[$message][2];
      }
    } else {
      return $message;
    }
  }

}
