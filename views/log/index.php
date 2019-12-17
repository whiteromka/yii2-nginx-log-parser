<?php

use app\models\Log;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $popularBrowsers array */
/* @var $queries array */

$this->title = 'Логи NginX';
?>

<h1><?= Html::encode($this->title) ?></h1>
<div class="alert alert-warning" role="alert">
    Я тестировал на ваших данных, т.е. 120 тыс строк логов за 1 день. После применения миграций для загрузки нужно использовать консольную
    команду <code>php yii log/upload [имя файла с логами]</code>. Файл с логами должен находится в директории <code>app\logNginx</code>.
</div>
<hr>

<div class="row">
    <div class="col-md-7">
        <h3>Запросы:</h3>
        <div id="myfirstchart" style="height: 250px;"></div>
    </div>
    <div class="col-md-5">
        <h3>Браузеры:</h3>
        <div id="donut-example"></div>
    </div>
</div>
<hr>

<div class="log-index">
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <h3>Данные:</h3>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ip',
            [
                'attribute' => 'date',
                'format' => ['date', 'php:d.m.Y H:i:s'],
            ],
            [
                'attribute' => 'url',
                'value' => function ($data) {
                    return $data->shortUrl(40);
                }
            ],
            'os',
            [
                'attribute' => 'x_bit',
                'filter'    => Log::getXBitList()
            ],
            'browser'
        ],
    ]); ?>
</div>
<script>
    let dataDonut =<?=json_encode($popularBrowsers)?>;
    let dataLine =<?=json_encode($queries)?>;

    // Линия
    new Morris.Line({
        element: 'myfirstchart',
        data: dataLine,
        xkey: 'time',
        ykeys: ['value'],
        labels: ['Value']
    });

    // Пончик
    new Morris.Donut({
        element: 'donut-example',
        data: dataDonut
    });
</script>
