<?php

namespace App;

class TransferredContact extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT con.ID contact_id,
                 con.ASSIGNED_BY_ID responsible_id,
                 CONCAT(IFNULL(LAST_NAME, ''), ' ', NAME, ' ', IFNULL(SECOND_NAME, '')) name
            FROM b_crm_contact con
                 INNER JOIN b_uts_crm_contact ucon
                ON con.ID = ucon.VALUE_ID
                 INNER JOIN b_crm_contact_company cco
                ON con.ID = cco.CONTACT_ID
            
                 INNER JOIN b_crm_company ccom
                ON ccom.ID = cco.COMPANY_ID
                 INNER JOIN b_uts_crm_company ucco
                ON ccom.ID = ucco.VALUE_ID
            
                 INNER JOIN b_user_field_enum ufe
                ON ucco.UF_CRM_1524464429 = ufe.ID
            AND ufe.USER_FIELD_ID = 182
            AND ufe.ID = 68
            WHERE ucon.UF_CRM_1551781165 < 1969
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            if (empty($el['name']) || empty($el['contact_id']))
                continue;
            $this->result[$el['contact_id']] = trim($el['name']);
        }
        asort($this->result);
//        print_r($this->result);
    }
}