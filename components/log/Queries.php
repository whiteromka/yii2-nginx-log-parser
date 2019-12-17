<?php

namespace app\components\log;

use app\models\Log;
use Yii;

/**
 * Class Queries
 *
 * В классе описана вся математическая логика для получения графика "Линия". Тут определяется массив
 * контрольных точек - меток времени и соответствующих для них значений - количества http запросов от
 * предыдущей точки до текущей.
 *
 * @package app\components\log
 */
class Queries
{
    /** @var Log  */
    public $log;

    /**
     * Queries constructor.
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /** Получить массив котрольный точек со значениями для графика
     * @param $where
     * @return array
     */
    public function getQueries($where)
    {
        // Получаем даты начала и конца для графика
        $dates = $this->getDates();
        $dateStart = $dates['dateStart'];
        $dateEnd = $dates['dateEnd'];

        // Получаем правильный и полный $where
        $where = $this->reconstructCondition($where, $dateStart, $dateEnd);

        // Получаем контрольные точки - даты для графика
        $arrDate = $this->getDatePoints($dateStart, $dateEnd);

        // Перебор контрольных точек c запросов в БД для получения значения запросов в каждой точке
        $arrResult = [];
        $i = 0;
        try {
            foreach ($arrDate as $one) {
                $arrDate[$i]['date1'];
                $arrDate[$i]['date2'];

                // Подстановка в условие контрольной точки
                foreach ($where as &$elementWhere) {
                    if (is_array($elementWhere)) {
                        if (isset($elementWhere[0]) && $elementWhere[0] == '>') {
                            $elementWhere[2] = $arrDate[$i]['date1'];
                        }
                        if (isset($elementWhere[0]) && $elementWhere[0] == '<') {
                            $elementWhere[2] = $arrDate[$i]['date2'];
                        }
                    }
                }

                $element = Log::find()->asArray()
                    ->select(['COUNT(id) as value'])
                    ->andFilterWhere($where)
                    ->one();

                $element['time'] = gmdate("Y-m-d H:i:s", $arrDate[$i]['date2']) ;
                $arrResult[] = $element;
                $i++;
            }
        } catch (\Exception $ex) {
            return [];
        }
        return $arrResult;
    }

    /** Вернет dateStart dateEnd в массиве
     * @return array
     */
    protected function getDates()
    {
        $dateStart = null;
        $dateEnd = null;
        if (Yii::$app->request->queryParams && isset(Yii::$app->request->queryParams['LogSearch'])) {

            $params = Yii::$app->request->queryParams['LogSearch'];

            $dateStart = (isset($params['dateStart']) && $params['dateStart']) ? $params['dateStart'] : null;
            $dateStart = $this->log->convertToUnixTime($dateStart);
            $dateEnd = (isset($params['dateEnd']) && $params['dateEnd']) ? $params['dateEnd'] : null;
            $dateEnd = $this->log->convertToUnixTime($dateEnd);
        }

        if (!$dateStart) {
            $dateStart = Log::find()->select(['id', 'date'])->asArray()->where(['id' => 1])->one()['date'];
        }
        if (!$dateEnd) {
            $count = Log::find()->count();
            $dateEnd = Log::find()->select(['id', 'date'])->asArray()->where(['id' => $count])->one()['date'];
        }
        return ['dateStart'=>$dateStart, 'dateEnd'=>$dateEnd];
    }

    /** Отформатирует условие $where и вернет его
     * @param $where
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function reconstructCondition($where, $dateStart, $dateEnd)
    {
        // Условие по умолчанию от самой первой даты до последней
        if (!$where) {
            $where = [
                'and',
                ['>', 'date', $dateStart],
                ['<', 'date', $dateEnd]
            ];
        }

        // Если в $where простой массив без 'and'
        $whereIsSimpleArray = true;
        foreach ($where as $element) {
            if (is_array($element)) {
                $whereIsSimpleArray = false;
                break;
            }
        } // то добавим в него дату начала и конца по умолчанию
        if ($whereIsSimpleArray) {
            $currentCondition = $where;
            $where = [
                'and',
                ['>', 'date', $dateStart],
                ['<', 'date', $dateEnd],
                $currentCondition
            ];
        }

        // Если $where это массив с вложенными элементами, то проверим существование даты начала и конца
        if (!$whereIsSimpleArray && strtolower($where[0]) == 'and') {
            $existDateStart = false;
            $existDateEnd = false;
            foreach ($where as $element) {
                if (is_array($element)) {
                    if ($element[0] == '>' && $element[0] == 'date') {
                        $existDateStart = true;
                    }
                    if ($element[0] == '<' && $element[0] == 'date') {
                        $existDateEnd = true;
                    }
                }
            }

            // Если даты начала и конца не существуют в массиве, то добавим их
            if (!$existDateStart) {
                array_push($where, ['>', 'date', $dateStart]);
            }
            if (!$existDateEnd) {
                array_push($where,   ['<', 'date', $dateEnd]);
            }
        }
        return $where;
    }

    /** Получить контрольные точки(даты) для графика
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getDatePoints($dateStart, $dateEnd)
    {
        // Определяем 1/N часть от времени
        $allTime = $dateEnd - $dateStart;
        $countPart = 12;
        $part = ceil($allTime / $countPart);

        // массив контрольных точек дат от и до, по ним строится график
        $arrDate = [];
        for ($i = $dateStart; $i < $dateEnd; $i+=$part) {
            $date1 = $i;
            $date2 = $date1 + $part;
            $arrDate[] = ['date1' => $date1 , 'date2' => $date2];
        }
        $element = array_pop($arrDate);
        $element['date2'] = $dateEnd;
        array_push($arrDate, $element);
        return $arrDate;
    }
}