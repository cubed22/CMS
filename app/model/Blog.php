<?php
namespace App\Model;

/**
 * Blog model class for managing blog posts and categories.
 */
class Blog extends LocalizedModel
{
    /** @var string */
    protected static $tableMain = "blog";

    /** @var string */
    protected static $tableLocale = "blog_lang";

    /** @var string */
    protected static $tableImage = "blog_images";

    /** @var string */
    protected static $recordClass = BlogRecord::class;

    /** @var string */
    protected static $recordImageClass = BlogImageRecord::class;

    /** @var string */
    protected static $relatedColumn = "blog_id";

    /**
     * Add a blog post to a category.
     *
     * @param int $recordId
     * @param int $categoryId
     * @return mixed
     */
    public function addToCategory( $recordId, $categoryId ) {
        $values = [];
        $values["blog_id"] = $recordId;
        $values["category_id"] = $categoryId;
        return $this->getDatabase()->table("blog_to_categories")->insert( $values );
    }

    /**
     * Remove a blog post from a category.
     *
     * @param int $recordId
     * @param int $categoryId
     * @return mixed
     */
    public function removeFromCategory( $recordId, $categoryId ) {
        return $this->getDatabase()->table("blog_to_categories")->where("blog_id", $recordId)->where("category_id", $categoryId)->delete();
    }
}

class BlogRecord extends LocalizedRecord
{
    /** @var string */
    protected static $tableImage = "blog_images";

    /** @var string */
    protected static $recordImageClass = BlogImageRecord::class;

    /**
     * Get the category of the blog post.
     *
     * @return BlogCategoryRecord|false
     */
    public function getCategory() 
    {
        $data = $this->data()->ref("category_id");

        if ($data !== NULL)
        return new BlogCategoryRecord($data);

        return false;
    }

    /**
     * Check if the blog post is in a specific category.
     *
     * @param int $categoryId
     * @return bool
     */
    public function isInCategory($categoryId)
    {
        $data = $this->data()->related("blog_to_categories")->where("category_id", $categoryId);

        return count($data);
    }

    /**
    * Get the categories of the blog post with optional filtering and ordering.
    *
    * @param string|null $where
    * @param string|null $order
    * @return array
    */
    public function categories($where = NULL, $order = NULL)
    {
        $data = $this->data()->related("blog_to_categories");

        if (!empty( $where))
            $data->where($where);

        if (!empty($order))
            $data->order($order);

        $result = [];
        foreach ($data as $d) {
            $object = new BlogToCategoryRecord($d);
            $result[] = $object->getCategory();
        }
        return $result;
    }
}

class BlogImageRecord extends BaseRecord
{

}

/**
 * Record class for the relationship between blog posts and categories.
 */
class BlogToCategoryRecord extends BaseRecord
{
    /**
     * Get the category associated with this blog post.
     *
     * @return BlogCategoryRecord|false
     */
    public function getCategory()
    {
        $data = $this->data()->ref("category_id");

        if ($data !== NULL)
            return new BlogCategoryRecord( $data );

        return false;
    }
}

class BlogCategories extends BaseModel
{
    /** @var string */
    protected static $tableMain = "blog_categories";

    /** @var string */
    protected static $recordClass = BlogCategoryRecord::class;
}

/**
 * Record class for blog categories.
 */
class BlogCategoryRecord extends BaseRecord
{
    /**
    * Get the blog posts associated with this category with optional filtering, ordering, and limiting.
    *
    * @param string|null $where
    * @param string|null $order
    * @param int|null $limit
    * @return array
    */
    public function items($where = NULL, $order = NULL, $limit = NULL)
    {
        $data = $this->data()->related("blog");

        if (!empty($where))
            $data->where($where);

        if (!empty($order))
            $data->order($order);

        if (!empty($limit))
            $data->limit($limit);

        $result = [];
        foreach ($data as $d) {
            $result[] = new BlogRecord($d);
        }
        return $result;
    }

}
