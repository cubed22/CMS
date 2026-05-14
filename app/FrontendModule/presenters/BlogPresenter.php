<?php

declare(strict_types=1);

namespace App\FrontendModule\Presenters;

use App\Model;
use App\Components\IPaginationControlFactory;
use App\Components\PaginationControl;

/**
 * Presenter for the blog section of the frontend, handling listing, details, and category filtering of blog posts.
 */
final class BlogPresenter extends FrontendPresenter
{
	/** @var Model\Blog */
	private Model\Blog $blog;

	/** @var Model\BlogCategories */
	private Model\BlogCategories $blogCategories;

	/** @var IPaginationControlFactory */
	private IPaginationControlFactory $paginationControlFactory;

	const ITEMS_PER_PAGE = 9;

	/**
	 * Blog presenter constructor.
	 * 
	 * @param IPaginationControlFactory $paginationControlFactory
	 * @param Model\Blog $blog
	 * @param Model\BlogCategories $blogCategories
	 * @return void
	 * 
	 */
	public function __construct( IPaginationControlFactory $paginationControlFactory, Model\Blog $blog, Model\BlogCategories $blogCategories )
	{
		parent::__construct();
		$this->blog = $blog;
		$this->blogCategories = $blogCategories;

		$this->paginationControlFactory = $paginationControlFactory;
	}

	/**
	 * Create pagination control component.
	 *
	 * @return PaginationControl
	 */
	protected function createComponentPagination(): PaginationControl
	{
		return $this->paginationControlFactory->create();
	}

	/**
	 * Before render lifecycle hook to prepare template variables.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->blogCategoriesItems = $this->blogCategories->findAll();
		$this->template->blogItems = $this->blog->findAll(["active" => 1]);
	}

	/**
	 * Action for displaying blog post details.
	 *
	 * @param string $url
	 * @return void
	 */
	public function actionDetail($url) 
	{
		$record = $this->blog->findByUrl($url);
    	if (!$record) 
      		$this->error("Článek nenalezen.");

		if (!$record->data()->active)
			$this->error("Článek nenalezen.");
		
		$this->template->record = $record;
		$this->template->images = $record->images();
	}

	/**
	 * Default action for blog listing, with optional pagination and category filtering.
	 *
	 * @param int $page
	 * @param string $category
	 * @return void
	 */
	public function actionDefault(int $page = 1, $category = "")
	{
		$this->template->category = $category;
	}

	/**
	 * Render default view with pagination and category filtering.
	 *
	 * @param int $page
	 * @param string $category
	 * @return void
	 */
	public function renderDefault(int $page = 1, $category = "") 
	{
		$where = ["active" => 1];

		if ($category) {
			$categoryRecord = $this->blogCategories->findByUrl($category);
			if ($categoryRecord) {
				$where[":blog_to_categories.category_id"] = $categoryRecord->data()->id;
			}
		}

		$items = $this->blog->findAll($where, "time DESC");
		
		$this['pagination']->itemsPerPage = self::ITEMS_PER_PAGE;
		$this['pagination']->totalItems = count($items);

		$page = $this['pagination']->page;
		$this->template->items = array_slice($items, ($page - 1) * self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE);
	}

	/**
	 * Handle category selection for filtering blog posts.
	 *
	 * @param mixed $category
	 * @return void
	 */
	public function handleSetCategory($category)
	{
		$payloadParams = [];

		$this->template->category = $category;
		$payloadParams["category"] = $category;

		$this->payload->url = $this->link("this", $payloadParams);

		$this->redrawControl("itemsSnippet");
	}
}
