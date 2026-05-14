<?php
namespace App\Model;

use Nette;

/**
 * Base model class providing common database operations for all models.
 */
class BaseModel
{
	use Nette\SmartObject;

	/** @var string */
	protected static $tableMain = '';

	/** @var string */
	protected static $tableImage = '';

	/** @var string */
	protected static $recordClass = '';

	/** @var string */
	protected static $recordImageClass = '';

	/** @var Nette\Database\Explorer */
	private $database;

    /**
     * BaseModel constructor.
     * 
     * @param Nette\Database\Explorer $database
     * @return void
     */
    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Get the database connection.
     *
     * @return Nette\Database\Explorer
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Find a record by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id)
    {
        $data = $this->getDatabase()->table(static::$tableMain)->where("id", $id)->fetch();

        if ($data)
            return new static::$recordClass($data);

        return false;
    }

    /**
     * Find a record by URL.
     *
     * @param string $url
     * @return mixed
     */
    public function findByUrl($url)
    {
        $data = $this->getDatabase()->table(static::$tableMain)->where("url", $url)->fetch();

        if ($data)
            return new static::$recordClass($data);

        return false;
    }

    /**
     * Find a record by a specific property.
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function findByPropertyOne($property, $value)
    {
        $data = $this->getDatabase()->table(static::$tableMain)->where($property, $value)->limit(1)->fetch();

        if ($data)
            return new static::$recordClass($data);

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
    public function findAll($where = NULL, $order = NULL, $limit = NULL)
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
            $result[] = new static::$recordClass($d);
        } 

        return $result;
    }

    /**
     * Find an image record by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function findImage($id)
    {
        $data = $this->getDatabase()->table(static::$tableImage)->where("id", $id)->fetch();

        if ($data)
        return new static::$recordImageClass($data);

        return false;
    }

    /**
     * Insert a new record.
     *
     * @param array $values
     * @return Nette\Database\Table\IRow
     */
    public function insert($values)
    {
        unset($values['id']);
        return $this->getDatabase()->table(static::$tableMain)->insert($values);
    }

    /**
     * Insert a new image record.
     *
     * @param array $values
     * @return Nette\Database\Table\IRow
     */
    public function insertImage($values)
    {
        return $this->getDatabase()->table(static::$tableImage)->insert($values);
    }

    /**
     * Remove a record by ID.
     *
     * @param int $id
     * @return int
     */
    public function remove($id)
    {
        return $this->getDatabase()->table(static::$tableMain)->where("id", $id)->delete();
    }

    /**
     * Remove an image record by ID.
     *
     * @param int $id
     * @return int
     */
    public function removeImage($id)
    {
        return $this->getDatabase()->table(static::$tableImage)->where("id", $id)->delete();
    }
}

/**
 * Base record class representing a single database record with common operations.
 */
class BaseRecord
{
	use Nette\SmartObject;

	/** @var string */
	protected static $tableImage = '';

	/** @var string */
	protected static $recordImageClass = '';

	/** @var Nette\Database\Table\ActiveRow */
	private $data;

    /**
     * BaseRecord constructor.
     *
     * @param Nette\Database\Table\ActiveRow $data
     */
	public function __construct(Nette\Database\Table\ActiveRow $data)
	{
		$this->data = $data;
	}

    /**
     * Get the underlying data row.
     *
     * @return Nette\Database\Table\ActiveRow
     */
	public function data()
	{
		return $this->data;
	}

    /**
     * Update the record with new values.
     *
     * @param array $values
     * @return int
     */
	public function update($values)
    {
        unset($values['id']);
        return $this->data()->update($values);
    }

    /**
     * Get the user who created this record.
     *
     * @return UserRecord|false
     */
    public function getCreateUser() 
    {
        $data = $this->data()->ref("create_user_id");

        if ($data !== NULL)
            return new UserRecord($data);

        return false;
    }

    /**
     * Get the user who last modified this record.
     *
     * @return UserRecord|false
     */
    public function getModifyUser() 
    {
        $data = $this->data()->ref("modify_user_id");

        if ($data !== NULL)
            return new UserRecord($data);

        return false;
    }

    /**
     * Get related image records with optional filtering, ordering, and limiting.
     *
     * @param string|null $where
     * @param string|null $order
     * @param int|null $limit
     * @return array
     */
    public function images($where = NULL, $order = NULL, $limit = NULL)
    {
        $data = $this->data()->related(static::$tableImage);

        if (!empty($where))
            $data->where($where);

        if (!empty($order))
            $data->order($order);

        if (!empty($limit))
            $data->limit($limit);

        $result = [];
        foreach ($data as $d) {
            $result[] = new static::$recordImageClass($d);
        }
        return $result;
    }
}

