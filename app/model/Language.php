<?php
namespace App\Model;

/**
 * Language model class for managing language entries in the CMS.
 */
class LanguageModel extends BaseModel
{
  /** @var string */
  protected static $tableMain = "language";

  /** @var string */
  protected static $recordClass = LanguageRecord::class;

}

/**
 * Language record class representing a single language entry.
 */
class LanguageRecord extends BaseRecord
{
  
}