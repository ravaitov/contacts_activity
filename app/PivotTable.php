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
                            ],
                        'group' => $group,
                        'responsible' => $this->servingCompany->result[$company]['responsible'] ?? '',
                        'contact' => [
                            'web' => "<a onClick=\"jump2Contakt('$contact');\" >" . $el['name'] . "</a>",
                            'name' => $el['name'],
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
        $tr = function (string $x): string {
            return $x == '-' ? 'ЯЯЯ' : $x;
        };
        usort($data, fn($a, $b) => $tr($a['manager']) <=> $tr($b['manager']));

        $this->result = ['data' => $data, 'weeks' => $this->inputsData->weekNums()];
//        $this->log(print_r($this->result, 1));
    }

    private function productResult(int $company, int $contact): array
    {
        $del = [];
        $result = $this->contactComplect234->result[$company][$contact] ?? [$this->contactComplect234->fillProduct()];
        foreach ($result as $id => &$prod) {
            $weekData = $this->inputsData->weeksInputs(
                $company,
                $prod['ide_product'] ?? '',
                $prod['complect'] ?? '',
                $prod['tech_type'] ?? '',
                $prod['login'] ?? '',
                $prod['fio4ois'] ?? '',
            );
            if ($weekData && !$this->blocked('ois', $prod['fio4ois'])) {
                $prod = array_merge($prod, $weekData);
            } else {
                $del[] = $id;
            }
            foreach ($del as $id) {
                unset($result[$id]);
            }
        }

        return $result;
    }
}