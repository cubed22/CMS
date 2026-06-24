<?php
namespace App\Model;

use Nette;
use App\Model;

/**
 * Base model class providing common database operations for all models.
 */
class LocalizedModel extends BaseModel
{
	use Nette\SmartObject;

	/** @var string */
	protected static $tableLocale = '';

    /** @const LANG_POSTFIX Pripona pro tabulky s lokalizacemi */
    const LANG_POSTFIX = '_lang';

    /**
     * BaseModel constructor.
     * 
     * @param Nette\Database\Explorer $database
     * @return void
     */
    public function __construct(Nette\Database\Explorer $database)
    {
        parent::__construct($database);
    }

    /**
     * Find all records with optional filtering, ordering, and limiting.
     *
     * @param string|null $where
     * @param string|null $order
     * @param int|null $limit
     * @return array
     */
    public function findAll($locale = NULL, $where = NULL, $order = NULL, $limit = NULL)
    {
        $result = [];

        $data = $this->getDatabase()->table(static::$tableMain);

        if ($where)
        $data->where($where);

        if ($order)
        $data->order($order);

        if ($limit)
        $data->limit($limit);

        foreach ($data as $d) {
            $result[] = new static::$recordClass($d, $locale);
        } 

        return $result;
    }

}

/**
 * LocalizedRecord class representing a single record in the database with localization support.
 */
class LocalizedRecord extends BaseRecord
{
	use Nette\SmartObject;

	/** @var string */
	protected static $tableLocal = '';

	/** @var string Lokalizace zaznamu */
    private $lang;

    /**
     * LocalizedRecord constructor.
     *
     * @param Nette\Database\Table\ActiveRow $data
     */
	public function __construct(Nette\Database\Table\ActiveRow $data, ?string $lang = null)
	{
        parent::__construct($data);
		$this->lang = $lang;
	}

    /**
     * Get the current language of the record.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set the language of the record.
     *
     * @param string $lang
     * @return void
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Get the localized data for the record based on the current language.
     *
     * @param string|null $locale
     * @return Nette\Database\Table\ActiveRow|null
     */
    public function locale($locale = NULL)
    {
        if ( !isset( $locale ) )
            $locale = $this->lang;

        $result = $this->data()->related($this->data()->getTable()->getName() . LocalizedModel::LANG_POSTFIX)
                ->where("lang_id.shortcut", $locale)->fetch();

        return $result;
    }

    /**
     * Get all localized records related to this record.
     *
     * @return Nette\Database\Table\Selection
     */
    public function locales()
    {
        return $this->data()->related($this->data()->getTable()->getName() . LocalizedModel::LANG_POSTFIX);
    }

    /**
     * Update the record with new values.
     *
     * @param array $data
     * @param array $locals
     * @return int
     */
	public function update($data, $locals = [])
    {
        unset($values['id']);
        $this->data()->update($data);
        foreach ($locals as $locale => $values) {
            $this->locale($locale)->update($values);
        }
    }

}

