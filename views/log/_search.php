<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use app\models\Log;

/* @var $this yii\web\View */
/* @var $model app\models\LogSearch */
/* @var $form yii\widgets\ActiveForm */

$errors = $model->errors;
?>

<div class="log-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 0
        ],
    ]); ?>
    <div class="row">
        <div class="col-md-12">
            <h3>Фильтры:</h3>
        </div>
        <div class="col-md-3">
            <?  $dateError = (isset($errors['dateEnd']) && $errors['dateEnd']) ? $errors['dateEnd'][0] : '' ; ?>
            <div class="form-group field-logsearch-dateEnd <?=$dateError ? 'has-error' : '' ?>">
                <label class="control-label" for="logsearch-dateStart">Дата от ... до ...</label>
                <?=DatePicker::widget([
                    'language' => 'ru',
                    'name' => "LogSearch[dateStart]",
                    'value' => $model->dateStart,
                    'type' => DatePicker::TYPE_RANGE,
                    'name2' => "LogSearch[dateEnd]",
                    'value2' => $model->dateEnd,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy'
                    ]
                ]);?>
                <div class="help-block"><?=$dateError?></div>
            </div>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'os')->dropDownList(log::getList('os'), ['prompt' => 'Операционная система']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'x_bit')->dropDownList(Log::getXBitList()) ?>
        </div>

        <div class="col-md-3 m-t-23">
            <?= Html::submitButton('Применить', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Сбросить', ['log/index'], ['data-pjax' =>0, 'class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<hr>
