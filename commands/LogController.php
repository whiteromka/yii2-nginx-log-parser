<?php

namespace app\commands;

use Jenssegers\Agent\Agent;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Class LogController
 *
 * Контроллер для загрузки логов NginX. У контроллера есть 1 экшен Upload и вспомогательные protected методы в
 * которых реализован вся функционал, разбитый на логические части
 *
 * @package app\commands
 */
class LogController extends Controller
{
    /** @var Agent */
    public $agent;

    /** @var string */
    public $logFile;

    /** @var array */
    public $data = [];
    
    /** Загружает логи в БД
     * @param string $logFile файл с логами nginX
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionUpload($logFile = 'modimio.access.log')
    {
        $this->agent = new Agent();
        $this->logFile = $logFile;
        $this->read();
        $this->insertDb(1000);
        return ExitCode::OK;
    }

    /** Читает данные из файла Nginx */
    protected function read()
    {
        $file = "logNginx/$this->logFile";
        if (!file_exists($file)) {
            die('Error! File ' . $file . ' is not isset! ');
        }

        $handle = @fopen($file, "r");
        if ($handle) {
            echo 'Start read NginxLogs' . "\n" . '. = 1000 rows from NginxLogs' . "\n";
            $i = 0;
            while (($row = fgets($handle)) !== false) {
                $this->data[] = $this->parse($row);
                $i++;
                if ($i == 1000) {
                    echo '.';
                    $i = 0;
                }
            }
            if (!feof($handle)) {
                echo "Error: function fgets() failed!\n";
            }
        }
        fclose($handle);
        echo "\n" . 'Reading is finished! Total count - ' . count($this->data) . "\n";
    }

    /** Вернет распарсеные данные из строки
     * @param string $str
     * @return array
     */
    protected function parse(string $str)
    {
        $patternIp = '([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';
        $patternDate = '(\[.*\])';
        $patternUrl = '("http.*\s)?';
        $patternUserAgent = '(".*")';
        $pattern = '#' . $patternIp . '.*' . $patternDate . '.*' .$patternUrl . '.*' . $patternUserAgent . '#';
        preg_match($pattern, $str, $matches);
        
        $ip = (isset($matches[1]) && $matches[1]) ? $matches[1] : null;
        $date = (isset($matches[2]) && $matches[2]) ? trim($matches[2], '[]') : null;
        $date = $this->convertToUnix($date);
        $url = (isset($matches[3]) && $matches[3]) ? trim($matches[3],'"') : $this->getUrl($str);
        $userAgent = isset($matches[4]) ? trim($matches[4], '"') : null;
        $xBit = $this->getXBit($userAgent);

        /** @var Agent $agent */
        $agent = $this->agent;
        $agent->setUserAgent($userAgent);
        $robot = $agent->robot();

        $os = $agent->platform() ? $agent->platform() : $robot;
        $os = ($os == 0) ? null : $os;

        $browser = $agent->browser() ? $agent->browser() : $robot;
        $browser = ($browser == 0) ? null : $browser;

        return ['ip' => $ip, 'date' => $date, 'url' => $url, 'os'=> $os, 'x_bix' => $xBit, 'browser' => $browser];
    }

    /** Вернет url если он есть
     * @param string $str
     * @return string|null
     */
    protected function getUrl(string $str)
    {
        preg_match('#("http\S*")#', $str, $matches);
        return $url = (isset($matches[1]) ?? $matches[1]) ? trim($matches[1], '"') : null;
    }

    /** Вернет разрядность если указана
     * @param $userAgent
     * @return string|null
     */
    protected function getXBit($userAgent)
    {
        $xBit = null;
        if (!$userAgent) {
            return $xBit;
        }
        preg_match('#(\(.*\))#', $userAgent, $matches);
        $data = (isset($matches[1]) && $matches[1]) ? trim($matches[1], '()') : null;
        if ($data) {
            $xBit = strpos($data, '64') ? '64' : (strpos($data, '86') ? '86' : null);
        }
        return $xBit;
    }

    /** Подготовить записи, разбить по N штук
     * @param int $rowsForOnce
     * @throws \yii\db\Exception
     */
    protected function insertDb($rowsForOnce = 1000)
    {
        echo 'Start insert in Db' . "\n";
        $batch = [];
        foreach ($this->data as $key => $row) {
            $batch[] = $row;
            if ( !isset($this->data[1 + $key]) ) { // 2 завписываем остатки
                $this->saveInDB($batch);
                $batch = [];
            }
            if (count($batch) == $rowsForOnce) { // 1 завписываем по $rowsForOnce штук за раз
                $this->saveInDB($batch);
                $batch = [];
            }
        }
        echo "\n" . 'Finish insert in Db' . "\n";
    }

    /** Сохраняет
     * @param $batch
     * @throws \yii\db\Exception
     */
    protected function saveInDB($batch)
    {
        Yii::$app->db->createCommand()->batchInsert('log', ['ip', 'date', 'url', 'os', 'x_bit', 'browser'], $batch)->execute();
        echo '.';
    }

    /** Приводит время в UnixTime
     * @param $date
     * @return false|int|null
     */
    protected function convertToUnix($date)
    {
        if (!$date) {
            return null;
        }
        $date = str_replace('/', ' ', $date);
        $pos = strpos($date, ':');
        $date = $pos!==false ? substr_replace($date, ' ', $pos, strlen(':')) : $date;
        $utime = strtotime($date);
        return $utime;
    }
}