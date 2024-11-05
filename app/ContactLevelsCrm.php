<?php

namespace App;

class ContactLevelsCrm extends AbstractApp
{
    private array $influenceLevelNames;
    private array $usageLevelNames;

    public array $users;

    public function run(): void
    {
        $this->getLevelsId();
        $this->getInfluenceLevelNames();
        $this->getUserLevelNames();
        $this->getUsers();
        $this->formContacts();
//        print_r($this->result);
    }

    private function getLevelsId(): void
    {
        $sql = <<<SQL
            SELECT con.ID contact_id,
                 con.ASSIGNED_BY_ID responsible_id,
                 CONCAT(IFNULL(LAST_NAME, ''), ' ', NAME, ' ', IFNULL(SECOND_NAME, '')) name,
                 ucon.UF_CRM_1524141984 influence_level_id, -- Уровень влияния
                 ucon.UF_CRM_1551781165 usage_level_id, -- Уровень использования К+
                 cco.company_id
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
            if (empty($el['usage_level_id']))
                continue;
            $this->result[$el['company_id']][$el['contact_id']] = [
                'responsible_id' => $el['responsible_id'],
                'influence_level_id' => $el['influence_level_id'],
                'usage_level_id' => $el['usage_level_id'],
                'name' => $el['name'],
            ];
        }
    }

    private function getInfluenceLevelNames(): void
    {
        $sql = <<<SQL
            SELECT
            	ID id,
            	VALUE 
            FROM
            	b_user_field_enum
            WHERE
            	USER_FIELD_ID = (
            		SELECT
            			ID
            		FROM
            			b_user_field
            		WHERE
            			FIELD_NAME = 'UF_CRM_1551781165'
            	)		
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            $this->usageLevelNames[$el['id']] = $el['VALUE'];
        }
    }

    private function getUserLevelNames(): void
    {
        $sql = <<<SQL
            SELECT
            	ID id,
            	VALUE 
            FROM
            	b_user_field_enum
            WHERE
            	USER_FIELD_ID = (
            		SELECT
            			ID
            		FROM
            			b_user_field
            		WHERE
            			FIELD_NAME = 'UF_CRM_1524141984'
            	)		
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            $this->influenceLevelNames[$el['id']] = $el['VALUE'];
        }
    }

    private function getUsers(): void
    {
        $sql = <<<SQL
            SELECT
            	ID,
            	NAME,
            	LAST_NAME
            FROM
            	b_user
            WHERE
            	`ACTIVE` = 'Y'
            	AND LID = 's1'			
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            $this->users[$el['ID']] = $el['LAST_NAME'] . ' ' . $el['NAME'];
        }
    }

    private function formContacts(): void
    {
        foreach ($this->result as $company => $contacts)
             foreach ($contacts as $cid => $contact)   {
                 $this->result[$company][$cid]['usage_level'] = $this->usageLevelNames[$contact['usage_level_id']];
                 $this->result[$company][$cid]['influence_level'] = $this->influenceLevelNames[$contact['influence_level_id']] ?? '';
                 $this->result[$company][$cid]['responsible'] = $this->users[$contact['responsible_id']] ?? '';
        }
    }
}