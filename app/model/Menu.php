<?php
namespace App\Model;

/**
 * Menu model class for managing menu items.
 */
class Menu extends BaseModel
{
  /** @var string */
  protected static $tableMain = "menu";

  /** @var string */
  protected static $recordClass = MenuRecord::class;

}

/**
 * Menu record class representing a single menu.
 */
class MenuRecord extends BaseRecord
{
    /**
     * Get the menu items associated with this menu.
     *
     * @param string|null $where Optional WHERE clause for filtering items.
     * @param string|null $order Optional ORDER BY clause for sorting items.
     * @param int|null $limit Optional LIMIT for the number of items to retrieve.
     * @return MenuItemRecord[] Array of MenuItemRecord objects.
     */
    public function items($where = NULL, $order = NULL, $limit = NULL)
    {
        $data = $this->data()->related("menu_items");

        if (!empty($where))
            $data->where($where);

        if (!empty($order))
            $data->order($order);

        if (!empty($limit))
            $data->limit($limit);

        $result = [];
        foreach ($data as $d) {
            $result[] = new MenuItemRecord($d);
        }
        return $result;
    }
}

/**
 * Menu item record class representing a single menu item.
 */
class MenuItemRecord extends BaseRecord
{

}