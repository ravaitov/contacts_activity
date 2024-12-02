<?php

namespace App;

class PivotTable extends AbstractApp
{
    private ContactComplect234 $contactComplect234;
    private ManagerDisList90 $contactDisList90;
    private ContactLevelsCrm $contactLevelsCrm;
    private InputsData $inputsData;
    private CompanyName $companyList;
    private UserName $userList;
    private ServingCompany $servingCompany;

    public function __construct()
    {
        parent::__construct();
        $this->contactComplect234 = new ContactComplect234();
        $this->contactDisList90 = new ManagerDisList90();
        $this->contactLevelsCrm = new ContactLevelsCrm();
        $this->inputsData = new InputsData();
        $this->companyList = new CompanyName();
        $this->userList = new UserName();
        $this->servingCompany = new ServingCompany();
    }

    public function run(): void
    {
        $this->contactDisList90->run();
        $this->contactLevelsCrm->run();
        $this->contactComplect234->run();
        $this->inputsData->run();
        $this->userList->run();
        $this->companyList->run();
        $this->servingCompany->run();

        $cnt = 1;
        foreach ($this->contactLevelsCrm->result as $company => $contacts) {
            foreach ($contacts as $contact => $el) {
                $manager = $this->contactDisList90->result[$company][$contact] ?? '-';
                $group = $this->servingCompany->result[$company]['group'] ?? '-';
                if (
                    $this->blocked('sds', $manager)
                    || $this->blocked('contact', $contact)
                    || $this->blocked('dis', ($manager ?? '-') == '-' ? 'нет' : 'да')
                    || $this->blocked('group', $group)
//                    || !in_array($company, [965,966,1023,1026]) // debug
//                    || !in_array($company, [1026, 965]) // debug
//                    || !in_array($company, [1026]) // debug
                )
                    continue;
                if ($product = $this->productResult($company, $contact)) {
                    $data["$company|$contact"] = [
                        'company' => [
                            'web' => "<a onClick=\"jump2Company('$company');\" >" . $this->companyList->result[$company] . "</a>",
                            'name' => $this->companyList->result[$company],
                            'id' => $company,
                        ],
                        'group' => $group,
                        'responsible' => $this->servingCompany->result[$company]['responsible'] ?? '',
                        'contact' => [
                            'web' => "<a onClick=\"jump2Contakt('$contact');\" >" . $el['name'] . "</a>",
                            'name' => $el['name'],
                            'id' => $contact,
                        ],
                        'manager' => $this->userList->result[$manager] ?? '-',
                        'usage_level' => $el['usage_level'],
                        'influence_level' => $el['influence_level'],
                        'products' => $product,
//                    '#id' => [$company, $contact, $manager],
                    ];
//                    if ($cnt++ > 2)
//                        break 2;
                }
            }
        }
        if (!$data) {
            $this->result = ['data' => [], 'weeks' => []];
            return;
        }
//        $this->log(print_r($data, 1));

        $tr = function (array $x): string {
            return $x['company']['name'] . '|' . $x['contact']['name'];
        };
        usort($data, fn($a, $b) => $tr($a) <=> $tr($b));

        $this->result = ['data' => $data, 'weeks' => $this->inputsData->weekNums()];
//        $this->log(print_r($this->result, 1));
    }

    private function productResult(int $company, int $contact): array
    {
        $del = [];
        $result = $this->contactComplect234->result[$company][$contact] ?? [$this->contactComplect234->fillZeroProduct()];
        foreach ($result as $id => &$prod) {
            $weekData = $this->inputsData->weeksInputs(
                $company,
                $prod['ide_product'] ?? '',
                $prod['complect'] ?? '',
                $prod['tech_type'] ?? '',
                $prod['login'] ?? '',
                $prod['fio4ois'] ?? '',
            );
            if ($weekData) {
                $prod = array_merge($prod, $weekData);
            } else {
                $del[] = $id;
            }
        }
        foreach ($del as $id) {
            unset($result[$id]);
        }

//        $this->log(print_r($result, 1));

        $userResult = $this->userResult($result);
        if ($this->blocked('total', $userResult))
            return [];

        foreach ($result as &$prod) {
            $prod[] = $userResult;
        }
//        $this->log(print_r($result, 1));

        return $result;
    }

    private function userResult(array $result): string // Итог пользователя
    {
        $weekCount = $this->inputsData->weekCount();
        $rowCount = count($result);

        if ($result[0]['ide_product'] == '-') { //Нет привязки
            return '';
        }

        foreach ($result as $prod) { // если один общий итог уже 1
            if ($prod[$weekCount * 3] == 1)
                return 1;
        }

        for ($id = 2, $res1 = $noLogin = $noInfo = 0; $id < $weekCount * 3; $id += 3) { // цикл по итогам (горизонтальн)
            for ($row = 0, $res0 = 0; $row < $rowCount; $row++) { // цикл по строкам (вертикально)
                $x = (string)$result[$row][$id];
                if ($x === '0')
                    $res0++;
                elseif ($x == 1)
                    $res1 = 1;
                elseif ($x == $this->inputsData->noLogin)
                    $noLogin++;
                elseif ($x == $this->inputsData->noInfo)
                    $noInfo++;
            }
            if ($res0 == $rowCount) // есть сквозной 0
                return 0;
        }
        if ($res1)
            return 1;

        return $noLogin > $noInfo ? $this->inputsData->noLogin : $this->inputsData->noInfo;
    }
}