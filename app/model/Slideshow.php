<?php
namespace App\Model;

/**
 * Slideshow model class for managing slideshow entries in the CMS.
 */
class Slideshow extends BaseModel
{
  /** @var string */
  protected static $tableMain = "slideshow";

  /** @var string */
  protected static $recordClass = SlideshowRecord::class;

}

/**
 * Slideshow record class representing a single slideshow entry.
 */
class SlideshowRecord extends BaseRecord
{
  
}