<?php
namespace App\Model;

/**
 * Log messages model class.
 */
class LogMessages extends BaseModel
{
  /** @var string */
  protected static $tableMain = "log_messages";

  /** @var string */
  protected static $recordClass = LogMessageRecord::class;

}

/**
 * Log message record class.
 */
class LogMessageRecord extends BaseRecord
{

}
