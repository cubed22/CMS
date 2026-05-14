<?php
namespace App\AdminModule\Presenters;

use App\Model;

/**
 * Presenter for the homepage of the admin panel, displaying recent items and statistics.
 */
final class HomepagePresenter extends AdminPresenter
{
    /** @var Model\Blog */
    private Model\Blog $blog;

    /** @var Model\BlogCategories */
    private Model\BlogCategories $blogCategories;

    /** @var Model\Users */
    private Model\Users $users;

    /**
     * Homepage presenter constructor.
     *
     * @param Model\Blog $blog
     * @param Model\BlogCategories $blogCategories
     * @param Model\Users $users
     */
    public function __construct(Model\Blog $blog, Model\BlogCategories $blogCategories, Model\Users $users)
    {
        parent::__construct();
        $this->blog = $blog;
        $this->blogCategories = $blogCategories;
        $this->users = $users;
    }

    /**
     * Startup lifecycle hook.
     *
     * @return void
     */
    public function startup(): void
    {
        parent::startup();
    }

    /**
     * Default action for homepage panel.
     *
     * @return void
     */
    public function actionDefault()
    {
        $this->template->headerName = "Hlavní stránka";

        $lastItem = false;
        $items = $this->blog->findAll(null, "time_create DESC", 1);
        if (count($items)) {
            $lastItem = $items[0];
        }
        $this->template->lastBlog = $lastItem;

        $lastItem = false;
        $items = $this->blogCategories->findAll(null, "time_create DESC", 1);
        if (count($items)) {
            $lastItem = $items[0];
        }
        $this->template->lastBlogCategory = $lastItem;

        $lastItem = false;
        $items = $this->users->findAll(null, null, 1);
        if (count($items)) {
            $lastItem = $items[0];
        }
        $this->template->lastUser = $lastItem;
    }
    
}

