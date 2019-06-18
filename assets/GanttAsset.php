<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GanttAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';

    public $sourcePath = '@npm/frappe-gantt/dist';
    public $js = [
        'frappe-gantt.js',
    ];

    public $css = [
        'frappe-gantt.css',
    ];
}