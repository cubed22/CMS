<?php
namespace App\Model;

/**
 * Cronmaster model class for managing cron jobs.
 */
class Cronmaster extends BaseModel
{
  /** @var string */
  protected static $tableMain = "cronmaster";

  /** @var string */
  protected static $recordClass = CronRecord::class;

}

/**
 * Cron record class representing a single cron job.
 */
class CronRecord extends BaseRecord
{
  
}