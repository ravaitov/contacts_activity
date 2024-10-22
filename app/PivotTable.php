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
                $data["$company|$contact"] = [
                    'company' => $this->companyList->result[$company] ?? "<span style=\"color:red\">ОШИБКА! ID=$company</span>",
                    'contact' => $this->contactLevelsCrm->result[$contact]['name'],
                    'manager' => $this->userList->result[$manager],
                    'usage_level' => $this->contactLevelsCrm->result[$contact]['usage_level'],
                    'influence_level' => $this->contactLevelsCrm->result[$contact]['influence_level'],
                    'products' => $this->productResult($company, $contact),
//                    '#id' => [$company, $contact, $manager],
                ];
            }
        }
        usort($data, fn($a, $b) => $a['manager'] <=> $b['manager']);
        $this->result = ['data' =>  $data, 'weeks' => $this->inputsData->weekList()];
//        $this->log(print_r($this->result,1));
    }

    private function productResult(int $company, int $contact): array
    {
        $result = $this->contactComplect234->result[$company][$contact] ?? [$this->contactComplect234->fillProduct()];
        foreach ($result as &$prod) {
            $weekData = $this->inputsData->weeksInputs(
                $company,
                $prod['ide_product'] ?? '',
                $prod['complect'] ?? '',
                $prod['tech_type'] ?? '',
                $prod['login'] ?? '',
                $prod['fio4ois'] ?? '',
            );
            $prod = array_values(array_merge($prod, $weekData));
        }

        return $result;
    }
}