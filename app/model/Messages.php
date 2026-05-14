<?php
namespace App\Model;

/**
 * Messages model class for managing messages and their associated log messages.
 */
class Messages extends BaseModel
{
  /** @var string */
  protected static $tableMain = "messages";

  /** @var string */
  protected static $recordClass = MessageRecord::class;
  
}

/**
 * Message record class representing a single message.
 */
class MessageRecord extends BaseRecord
{
    /**
     * Get the log messages associated with this message.
     *
     * @param string|null $where Optional WHERE clause for filtering log messages.
     * @param string|null $order Optional ORDER BY clause for sorting log messages.
     * @param int|null $limit Optional LIMIT for the number of log messages to retrieve.
     * @return LogMessageRecord[] Array of LogMessageRecord objects.
     */
    public function messages($where = NULL, $order = NULL, $limit = NULL)
    {
        $data = $this->data()->related("log_messages");

        if (!empty($where))
            $data->where($where);

        if (!empty($order))
            $data->order($order);

        if (!empty($limit))
            $data->limit($limit);

        $result = [];
        foreach ($data as $d) {
            $result[] = new LogMessageRecord($d);
        }
        return $result;
    }
}
