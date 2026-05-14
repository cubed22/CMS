<?php
namespace App\Model;

/**
 * Modules model class for managing modules in the CMS.
 */
class Modules extends BaseModel
{
  /** @var string */
  protected static $tableMain = "modules";

  /** @var string */
  protected static $recordClass = ModuleRecord::class;
  
}

/**
 * Module record class representing a single module.
 */
class ModuleRecord extends BaseRecord
{

}