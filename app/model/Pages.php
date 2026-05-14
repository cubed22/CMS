<?php
namespace App\Model;

/**
 * Pages model class for managing pages in the CMS.
 */
class Pages extends BaseModel
{
  /** @var string */
  protected static $tableMain = "pages";

  /** @var string */
  protected static $tableImage = "page_images";

  /** @var string */
  protected static $recordClass = PageRecord::class;

  /** @var string */
  protected static $recordImageClass = PageImageRecord::class;
}

/**
 * Page record class representing a single page.
 */
class PageRecord extends BaseRecord
{

}

/**
 * Page image record class representing a single page image.
 */
class PageImageRecord extends BaseRecord
{

}