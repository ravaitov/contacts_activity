<?php

namespace App;

use App\DataBase\DataBase;
use App\DataBase\DataBaseMs;
use App\Logger\Logger;
use PDO;

class AbstractApp
{
    public static array $params = [];

    protected Config $config;
    protected DataBase $baseReport;
    protected DataBase $baseB24;
    protected DataBase $baseMs;
    protected Logger $logger;
    protected string $appName;
    protected int $status = 400;
    protected ?array $body;

    public array $result = [];

    public function __construct()
    {
        $this->config = Config::instance();
        $a = explode('\\', get_class($this));
        $this->appName = end($a);
//        $this->config->setParam('app_name', $this->appName);
        $this->logger = Logger::instance();
        $this->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'));
//        $this->baseReport = new DataBase('db_report');
        $this->baseB24 = new DataBase('db_b24');
        $this->baseMs = new DataBaseMs('db_ms');
        $this->baseMs->handle()->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
    }

    public function __destruct()
    {
        $this->log('<<< Завершение: ' . $this->appName . "\n");
    }

    public function run(): void
    {
    }

    public function prepare(array $params = []): void
    {
    }

    public function log(string $log, int $level = 0): void
    {
        $this->logger->log($log, $level);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function clearCache(): void
    {
        $cacheFile = $this->cacheFile();
        if (unlink($cacheFile)) {
            $this->log("Удален файл $cacheFile");
        } else {
            $this->log("Не удалалось удалить файл $cacheFile");
        }
    }

    protected function lastDay(string $date, string $suffix = ' 23:59:59'): string
    {
        return date('Y-m-t' . $suffix, strtotime($date));
    }

    protected function  cacheFile(): string
    {
        $date = str_replace('-', '', static::$params['date']);
        $weekCnt = static::$params['week'];

        return $this->config->conf('stor_dir') . "cache/{$weekCnt}_$date";
    }

    protected function validate(): void
    {
    }

    protected function assoc2Insert(array $insert): string
    {
        $fields = implode('`,`', array_keys($insert));
        $values = implode("','", array_values($insert));

        return "(`$fields`) values ('$values')";
    }

    protected function assoc2Update(array $update): string
    {
        $result = 'SET ';
        foreach ($update as $field => $value) {
            $result .= "`$field`='$value',";
        }
        return substr($result, 0, -1);
    }

    protected function blocked(string $key, string|int $val = 'default'): bool
    {
        if (!isset(static::$params[$key]) || static::$params[$key] === 'default')
            return false;

        if ($key == 'ois')
            return $val ? false : true;

        return static::$params[$key] == $val ? false : true;
    }

    protected function finish(): void
    {
    }
}