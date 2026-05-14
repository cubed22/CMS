<?php

namespace CheckSpam;

use Tracy\Debugger;

class CheckSpam
{
  /**
   * Zkontroluje hodnoty odeslaneho formulare a vygeneruje miru
   * pravdepodobnosti spamu. Staticka metoda - je mozno ji pouzit v jinych
   * tridach
   *
   * @param mixed $values Hodnoty odeslaneho formulare (reference)
   * @param bool $debug Pokud true, zobrazuji se hodnoty v ladence a loguji se
   * @return int Pravdepodobnost spamu ( 0 - vubec, 100 - urcite )
   */
  public static function check( &$values, $debug = false )
  {
    $spamLikelihood = 0;

    if ( $debug )
    {
      $values['IPaddress'] = $_SERVER['REMOTE_ADDR'];
      Debugger::log( $values, 'spam-debug-values' );
    }

    // Pokud vubec nebylo odeslano pole antipotvora, pak je to jasny robot
    // (nekteri roboti zamerne nevkladaji skryta pole)
    if ( !isset( $values['antipotvora'] ) || !isset( $values['antipotvora_kp'] ) || !isset( $values['antipotvora_mm'] ) )
    {
      $spamLikelihood += 100;
      return $spamLikelihood;
    }

    // Bylo vyplneno lakave, stylem schovane policko (honeypot)
    if ( !empty( $values['mail'] ) )
      $spamLikelihood += 100;

    // Bylo vyplneno lakave, stylem schovane policko (honeypot)
    if ( !empty( $values['website'] ) )
      $spamLikelihood += 100;

    // Zkontrolujeme delku provadeni skriptu (v sek.)
    if ( strlen( $values['antipotvora'] ) )
    {
      if ( is_numeric( $values['antipotvora'] ) )
      {
        // Pokud vyplneni trvalo velice kratce, pak je to podezrele
        if ( $values['antipotvora'] < 0.5 )
          $spamLikelihood += 75;
        elseif ( $values['antipotvora'] < 1 )
          $spamLikelihood += 50;
      }
      else
      {
        // Pokud v policku neco bylo, ale nebylo to cislo, je to robot :)
        $spamLikelihood += 100;
      }
    }
    else
    {
      // Pokud input s casem byl uplne prazdny (default), je to dost podezrele
      $spamLikelihood += 50;
    }

    // Zkontrolujeme pocet stiknutych klaves a kliknuti na inputy
    if ( strlen( $values['antipotvora_kp'] ) )
    {
      if ( is_numeric( $values['antipotvora_kp'] ) )
      {
        // Hlida se pocet stisknutych klaves javascriptem pri vyplnovani formu
        // (hodnoty jsou schvalne negativni, boti obcas i do tohoto inputu mohou
        // vlozit nejake cislo - cim mensi cislo, tim vice clovek)
        if ( $values['antipotvora_kp'] >= -1 )
          $spamLikelihood += 50;
        elseif ( $values['antipotvora_kp'] > -5 )
          $spamLikelihood += 25;
        elseif ( $values['antipotvora_kp'] > -10 )
          $spamLikelihood += 10;
      }
      else
      {
        // Pokud v policku neco bylo, ale nebylo to cislo, je to robot :)
        $spamLikelihood += 100;
      }
    }
    else
    {
      // Pokud input byl uplne prazdny (default), nemusi nutne znamenat robota
      // ale je to dost podezrele
      $spamLikelihood += 50;
    }
    // Zkontrolujeme pohyb mysi/touchscreenu
    if ( strlen( $values['antipotvora_mm'] ) )
    {
      if ( is_numeric( $values['antipotvora_mm'] ) )
      {
        // Hlida se pohyb mysi nad formularem
        // (hodnoty jsou schvalne negativni, boti obcas i do tohoto inputu mohou
        // vlozit nejake cislo - cim mensi cislo, tim vice clovek)
        if ( $values['antipotvora_mm'] >= -1 )
          $spamLikelihood += 25;
      }
      else
      {
        // Pokud v policku neco bylo, ale nebylo to cislo, je to robot :)
        $spamLikelihood += 100;
      }
    }
    else
    {
      // Pokud input byl uplne prazdny (default), nemusi nutne znamenat robota
      // ale je to podezrele (ale ne az tak jako u klaves - mys/pohyb obrazovky neni nutny)
      $spamLikelihood += 25;
    }

    // Nacteme podezrela slovicka ze slovniku
    $spamWords = [];
    $spamFile  = __DIR__ . '/spam.txt';
    $data      = file( $spamFile );
    foreach ( $data as $line )
    {
      $spamWords[] = trim( $line );
    }

    // Otestujeme na pripadna podezrela slovicka...
    foreach ( $values as $value )
    {

      if ( is_array( $value ) )
        continue;

      $value = strtolower( (string) $value );
      foreach ( $spamWords as $spamWord )
      {
        $spamWord = strtolower( $spamWord );
        $pos      = strpos( $value, $spamWord );
        if ( $pos !== false )
        {
          // Zakladni sazba pro slovo nalezene kdekoliv (5 bodu za nalez)
          $points  = 5;
          // Pokud jde o cele slovo, tj. slovo je z obou stran ohraniceno
          // sazba se navysuje (za kazde 25 bodu)
          $matches = preg_match_all( "/\b" . $spamWord . "\b/i", $value );
          if ( $matches )
            $points  = 25 * $matches;

          $spamLikelihood += $points;
        }
      }

      // Kontrola na vyskyt odkazu (15 bodu za odkaz)
      $matches = preg_match_all( "/<\/a>|\[\/url\]/i", $value );
      if ( $matches )
      {
        $spamLikelihood += 15 * $matches;
      }
    }

    // Odstranime z hodnot formulare ty spojene s antispamem
    unset( $values["mail"], $values["website"], $values["antipotvora"], $values["antipotvora_kp"], $values["antipotvora_mm"] );

    if ( $debug )
    {
      Debugger::log( $_SERVER['REMOTE_ADDR'] . " - Vyhodnocena pravdepodobnost, ze se jednalo o spam: " . $spamLikelihood, 'spam-debug-msg' );
    }

    return $spamLikelihood;
  }

}