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
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        /*"//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css"*/
    ];
    public $js = [
        /*"//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js",
        "//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js",
        "//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"*/

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
