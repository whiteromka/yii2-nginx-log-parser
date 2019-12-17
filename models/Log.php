<?php

namespace app\models;

use app\components\log\Queries;
use Yii;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property string|null $ip
 * @property int|null $date
 * @property string|null $url
 * @property string|null $os
 * @property string|null $x_bit
 * @property string|null $browser
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'integer'],
            [['url'], 'string'],
            [['ip', 'os', 'x_bit', 'browser'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'date' => 'Дата и время',
            'url' => 'Url',
            'os' => 'ОС',
            'x_bit' => 'Разрядность',
            'browser' => 'Браузер',
        ];
    }

    /** Переводит в UnixTime
     * @param $strData
     * @return false|int
     */
    public function convertToUnixTime($strData)
    {
        try{
            $arrDate = explode('.', $strData);
            $date = implode('/',[$arrDate[2], $arrDate[1], $arrDate[0]]);
            return $timestamp = strtotime($date);
        } catch (\Exception $ex) {
           return null;
        }

    }

    /** Укорачивает url до $toLength (не сохраняет его )
     * @param int $toLength
     * @return string|null
     */
    public function shortUrl($toLength = 50)
    {
        $len = strlen($this->url);
        if ($len > $toLength) {
            return substr($this->url, 0, $toLength). '...';
        }
        return $this->url;
    }

    /** Вернет список Разрядностей системы
     * @return array
     */
    public static function getXBitList()
    {
        return [ null => "Разрядность", "86" => "x86", "64" => "x64"];
    }

    /** Вернет список OS или Браузеров в зависимости от параметра
     * @param string $column только в значении 'os' или 'browser '
     * @return array|mixed|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public static function getList(string $column)
    {
        if (($column == 'os') || ($column == 'browser')) {
            $cache = Yii::$app->cache;
            $data = $cache->getOrSet($column, function() use ($column) {
                $data = Log::find()->select($column)->distinct()->asArray()->all();
                $arrData = ArrayHelper::map($data, $column, $column);
                return $arrData ;
            }, 60 * 60 * 24);
            return $data;
        }
        throw new \Exception('Static function getList() for class Log expect only "os" or "browser" as param');
    }

    /**
     * @param $where
     * @return array
     */
    public function getQueries($where)
    {
        $queries = new Queries($this);
        return $queries->getQueries($where);
    }
}
