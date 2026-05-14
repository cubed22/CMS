<?php

namespace App\FrontendModule\Translator;

use Nette;

/**
 * FrontendTranslator is a simple implementation of Nette\Localization\Translator for the frontend.
 * It provides basic translation functionality based on predefined word forms.
 */
class FrontendTranslator implements Nette\Localization\Translator
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

    $words["image"] = [ "obrázek", "obrázky", "obrázků"];
    $words["items"] = [ "položka", "položky", "položek" ];
    
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
