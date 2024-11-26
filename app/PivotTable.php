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
                    || $this->blocked('dis', $manager == '-' ? 'нет' : 'да')
                    || $this->blocked('group', $group)
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
//            if ($weekData && !$this->blocked('ois', $prod['fio4ois'])) {
            if ($weekData) {
                $prod = array_merge($prod, $weekData);
            } else {
                $del[] = $id;
            }
            foreach ($del as $id) {
                unset($result[$id]);
            }
        }

        $userResult = $this->userResult($result);
        if ($this->blocked('total', $userResult))
            return [];

        foreach ($result as &$prod) {
            $prod[] = $userResult;
        }
//        $this->log(print_r($result, 1));

        return $result;
    }

    private function userResult(array $result): string
    {
        $weekCount = $this->inputsData->weekCount();
//        $this->log("weekCount=$weekCount");

        if (count($result) == 1) {
            return $result[0][$weekCount * 3];
        }

        foreach ($result as $prod) { // если один общий итог уже 1
            if ($prod[$weekCount * 3] == 1)
                return 1;
        }

        for ($id = 2, $res = ''; $id < $weekCount * 3; $id += 3) { // в каждой недели есть итог 1
            foreach ($result as $prod) {
//                $this->log("prod[$id]=".$prod[$id]);
                if ($prod[$id] == 1) {
                    $res = 1;
                    break;
                }
                $res = '';
            }
            if ($res != 1) {
                break;
            }
        }
//        $this->log("res=$res");
        if ($res)
            return 1;

        $resOk = 1;
        $resPred = 1;
        for ($id = 2; $id < $weekCount * 3; $id += 3) { // чередование н/д и 1
            $res = '';
            foreach ($result as $prod) {
                if ($prod[$id] == 1) {
                    $res = 1;
                    break;
                }
                $res = $res ?: $prod[$id];
            }
            if (!$res)
                return 0;
            if ($resPred != 1 && $res != 1) {
                $resOk = $res;
            }
            $resPred = $res;
        }
//        $this->log("resOk=$resOk");
        return $resOk;
    }
}