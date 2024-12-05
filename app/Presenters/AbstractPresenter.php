<?php

namespace App\Presenters;

use App\Config;
use App\Logger\Logger;

class AbstractPresenter
{
    protected array $fieldMapper = [ // порядок важен!
        'company' => 'Компания',
        'group' => 'Группа',
        'responsible' => 'Ответственный',
        'contact' => 'Контакт',
        'manager' => 'Менеджер',
        'usage_level' => 'Уровень использования К+',
        'influence_level' => 'Уровень влияния',
        'ide_product' => 'Сокращенное название',
        'complect' => 'Комплект',
        'version' => 'Версия',
        'tech_type' => 'Тех. тип',
//        'login' => 'Логин (онлайн)',  // скрыть логин
//        'fio4ois' => 'ФИО (для ОИС)', // скрыть логин
    ];
    protected array $data;
    protected Logger $logger;
    protected int $colNums;
    protected Config $config;


    public function __construct(array $data)
    {
        $this->config = Config::instance();
        $this->logger = Logger::instance();
        $a = explode('\\', get_class($this));
        $this->appName = end($a);
        $this->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'));
        $this->data = $data;
        $this->colNums = count($this->fieldMapper);
    }

    public function __destruct()
    {
        $this->log('<<< Завершение: ' . $this->appName);
    }

    public function sendTable(): void
    {
    }

    public function log(string $log, int $level = 0): void
    {
        $this->logger->log($log, $level);
    }
}