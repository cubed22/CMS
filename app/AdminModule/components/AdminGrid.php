<?php

namespace App\Components;

use Nette\ComponentModel\IContainer;
use App\AdminModule\Translator\AdminTranslator;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Nette\Utils\Html;

class AdminGrid extends \Ublaboo\DataGrid\DataGrid
{
    use \Nette\SmartObject;

    public function __construct(?IContainer $parent = null, ?string $name = null)
    {
        parent::__construct($parent, $name);
        $translator = new AdminTranslator();
        $this->setTranslator($translator);
    }

    /**
     * Add a DateTime column with automatic Unix timestamp filtering
     * 
     * @param string $key Column key in data source
     * @param string $name Display name
     * @param string|null $column Column name in database (if different from $key)
     * @param bool $filter Whether to enable filtering for this column
     * @return ColumnDateTime
     */
    public function addColumnDateTimeUnix($key, $name, $column = null, $filter = false)
    {
        $column = $column ?? $key;
        
        $col = $this->addColumnDateTime($key, $name, $column);

        if ($filter) {
        
        // Automatically set Unix timestamp filter condition
        $col->setFilterDate()
            ->setCondition(function($dataSource, $value) use ($column) {
                // If no value provided, skip filtering
                if (empty($value)) {
                    return;
                }
                
                // Convert Unix timestamp string to integer
                $timestamp = (int)$value;
                
                // Set range: start of day (00:00:00) to end of day (23:59:59)
                $dayStart = $timestamp;
                $dayEnd = $timestamp + 86399;
                
                // Apply filter to data source - use the column parameter
                $dataSource->where("{$column} >= ? AND {$column} <= ?", $dayStart, $dayEnd);
            });
        }
        
        return $col;
    }

    /**
     * Add an image column with flexible configuration
     *
     * @param string $key Column key in data source (used as default for image filename if $imagePath is string)
     * @param string $name Display name
     * @param string|callable $imagePath Image path - string prefix (e.g. '/www/upload/blog/small/') or callable that receives $row and returns full path
     * @param string|callable $altText Alternative text - string value or callable that receives $row and returns alt text
     * @param string $width CSS width (default: '40px')
     * @param string $height CSS height (default: '40px')
     * @param string|null $columnDbName Column name in database (if different from $key)
     * @return \Ublaboo\DataGrid\Column\ColumnText
     */
    public function addColumnImage(
        $key,
        $name,
        $imagePath = '',
        $altText = '',
        $columnDbName = null,
        $width = '40px',
        $height = '40px'
    ) {
        $col = $this->addColumnText($key, $name, $columnDbName ?? $key);
        
        // Get base path from presenter
        $basePath = $this->getPresenter()->getHttpRequest()->getUrl()->basePath;
        
        $col->setRenderer(function($row) use ($basePath, $imagePath, $altText, $width, $height, $key) {
            // Process image path
            $src = $basePath;
            if (is_callable($imagePath)) {
                $src .= $imagePath($row);
            } else {
                $src .= $imagePath . ($row[$key] ?? '');
            }
            if ($src === $basePath) {
                // If no image path is provided, return empty string
                $src .= '/www/admin/img/noimage.jpg';
            }
            
            // Process alt text
            $alt = '';
            if (is_callable($altText)) {
                $alt = $altText($row);
            } else if ($altText !== '') {
                $alt = $altText;
            }
            
            return Html::el('img')
                ->src($src)
                ->alt($alt)
                ->style("max-width: {$width}; max-height: {$height};");
        })->setAlign('center');
        
        return $col;
    }
}