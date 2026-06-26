<?php
namespace App\Model;

use Nette;

/**
 * Base model class providing common database operations for all models.
 */
class LocalizedModel extends BaseModel
{
	use Nette\SmartObject;

	/** @var string */
	protected static $tableLocale = '';

    /** @var string */
	protected static $relatedColumn = '';

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
     * Find a record by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id, ?string $locale = null)
    {
        $data = $this->getDatabase()->table(static::$tableMain)->where("id", $id)->fetch();

        if ($data)
            return new static::$recordClass($data, $locale);

        return false;
    }

    /**
     * Find a record by URL.
     *
     * @param string $url
     * @return mixed
     */
    public function findByUrl($url, ?string $locale = null)
    {
        $localeData = $this->getDatabase()->table(static::$tableLocale)->where("url", $url)->where("lang_id.shortcut", $locale)->fetch();

        if ($localeData) {
            $data = $this->getDatabase()->table(static::$tableMain)->where("id", $localeData[static::$relatedColumn])->fetch();

            if ($data)
                return new static::$recordClass($data, $locale);
        }

        return false;
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

        if ($locale) 
            $data->where([":" . static::$tableLocale . ".lang_id.shortcut" => $locale]);

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


    /**
     * Query all records with optional filtering, ordering, and limiting.
     *
     * @param string|null $locale
     * @param string|null $where
     * @param string|null $order
     * @param int|null $limit
     * @return Nette\Database\Table\Selection
     */
    public function queryAll($locale = NULL, $where = NULL, $order = NULL, $limit = NULL)
    {
        $selection = $this->getDatabase()->table(static::$tableMain);

        if ($locale)
            $selection->where([":" . static::$tableLocale . ".lang_id.shortcut" => $locale]);

        if ($where)
            $selection->where($where);

        if ($order)
            $selection->order($order);

        if ($limit)
            $selection->limit($limit);

        return $selection;
    }

    /**
     * Insert a new record with optional localized data.
     *
     * @param array $values
     * @param array $locals
     * @return Nette\Database\Table\IRow
     */
    public function insert($values, $locals = [])
    {
        unset($values['id']);
        $row = $this->getDatabase()->table(static::$tableMain)->insert($values);

        foreach ($locals as $locale => $data) {
            $lang = $this->getDatabase()->table('language')->where('shortcut', $locale)->fetch();
            $data["lang_id"] = $lang->id;
            $data[static::$relatedColumn] = $row->id;
            $this->getDatabase()->table(static::$tableLocale)->insert($data);
        }

        return $row;
    }

    /**
     * Insert a localized record for a specific ID and locale.
     *
     * @param int $id
     * @param string $locale
     * @param array $values
     * @return Nette\Database\Table\IRow
     */
    public function insertLocale($id, $locale, $values = [])
    {
        $lang = $this->getDatabase()->table('language')->where('shortcut', $locale)->fetch();
        $values["lang_id"] = $lang->id;
        $values[static::$relatedColumn] = $id;
        return $this->getDatabase()->table(static::$tableLocale)->insert($values);
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
        unset($data['id']);
        $this->data()->update($data);
        foreach ($locals as $locale => $values) {
            $this->locale($locale)->update($values);
        }
    }

}

