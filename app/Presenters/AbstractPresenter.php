<?php

namespace App\Presenters;

use App\Logger\Logger;

class AbstractPresenter
{
    protected array $fieldMapper = [
        'company' => 'Компания',
        'contact' => 'Контакт',
        'manager' => 'Менеджер',
        'usage_level' => 'Уровень использования К+',
        'influence_level' => 'Уровень влияния',
        'products' => '',
        'ide_product' => 'Сокращенное название',
        'complect' => 'Комплект',
        'version' => 'Версия',
        'net_type' => 'Сетевитость',
        'tech_type' => 'Тех. тип',
        'login' => 'Логин (онлайн)',
        'fio4ois' => 'ФИО (для ОИС)',
    ];
    protected array $data;
    protected Logger $logger;

    public function __construct(array $data)
    {
        $this->logger = Logger::instance();
        $this->data = $data;
    }

    public function log(string $log, int $level = 0): void
    {
        $this->logger->log($log, $level);
    }
}