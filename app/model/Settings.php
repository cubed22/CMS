<?php
namespace App\Model;

/**
 * Settings model class for managing application settings in the CMS.
 */
class Settings extends BaseModel
{
  /** @var string */
  protected static $tableMain = "settings";

  /** @var string */
  protected static $recordClass = SettingRecord::class;
  
}

/**
 * Setting record class representing a single setting.
 */
class SettingRecord extends BaseRecord
{

}


/**
 * Terms and Conditions model class for managing terms and conditions content.
 */
class TermsConditions extends BaseModel
{
  /** @var string */
  protected static $tableMain = "terms_conditions";

  /** @var string */
  protected static $recordClass = TermsConditionRecord::class;

}

/**
 * Terms and Conditions record class representing a single terms and conditions entry.
 */
class TermsConditionRecord extends BaseRecord
{

}

/**
 * Personal Data Protections model class for managing personal data protection content.
 */
class PersonalDataProtections extends BaseModel
{
  /** @var string */
  protected static $tableMain = "personal_data_protection";

  /** @var string */
  protected static $recordClass = PersonalDataProtectionRecord::class;

}

/**
 * Personal Data Protection record class representing a single personal data protection entry.
 */
class PersonalDataProtectionRecord extends BaseRecord
{
  
}