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

    public function __construct()
    {
        parent::__construct();
        $this->contactComplect234 = new ContactComplect234();
        $this->contactDisList90 = new ManagerDisList90();
        $this->contactLevelsCrm = new ContactLevelsCrm();
        $this->inputsData = new InputsData();
        $this->companyList = new CompanyName();
        $this->userList = new UserName();
    }

    public function run(): void
    {
        $this->contactDisList90->run();
        $this->contactLevelsCrm->run();
        $this->contactComplect234->run();
        $this->inputsData->run();
        $this->userList->run();
        $this->companyList->run();

        foreach ($this->contactDisList90->result as $company => $contacts) {
            foreach ($contacts as $contact => $el) {
                if (empty($this->contactLevelsCrm->result[$contact]))
                    continue;
                $manager = $this->contactDisList90->result[$company][$contact];
                if ($this->blocked('sds', $manager) || $this->blocked('contact', $contact))
                    continue;
                if ($product = $this->productResult($company, $contact)) {
                    $data["$company|$contact"] = [
                        'company' => "<a onClick=\"jump2Company('$company');\" >" . $this->companyList->result[$company]
                            ?? "<span style=\"color:red\">ОШИБКА! ID=$company</span>" . "</a>",
                        'contact' => "<a onClick=\"jump2Contakt('$contact');\" >"
                            . $this->contactLevelsCrm->result[$contact]['name'] . "</a>",
                        'manager' => $this->userList->result[$manager],
                        'usage_level' => $this->contactLevelsCrm->result[$contact]['usage_level'],
                        'influence_level' => $this->contactLevelsCrm->result[$contact]['influence_level'],
                        'products' => $product,
//                    '#id' => [$company, $contact, $manager],
                    ];
                }
            }
        }
        if (!$data) {
            $this->result = ['data' => [], 'weeks' => []];
            return;
        }
        usort($data, fn($a, $b) => $a['manager'] <=> $b['manager']);
        $this->result = ['data' => $data, 'weeks' => $this->inputsData->weekNums()];
//        $this->log(print_r($this->result,1));
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
                $prod = array_values(array_merge($prod, $weekData));
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