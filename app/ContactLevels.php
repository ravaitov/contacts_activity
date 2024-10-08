<?php

namespace App;

class ContactLevels extends AbstractApp
{
    private array $levels;
    private array $influenceLevelNames;
    private array $usageLevelNames;
    private array $responsibleNames;

    public array $users;

    public function run(): void
    {
        $this->getLevelsId();
        $this->getInfluenceLevelNames();
        $this->getUserLevelNames();
        $this->getUsers();
        $this->formContacts();
//        foreach (($this->result) as $id => $el) {
//            if (empty($el['usage_level']) || empty($el['influence_level'])) {
//                $this->log(print_r($el, 1));
//            }
//        }
//        $this->log(print_r($this->result, 1));
//        print_r($this->influenceLevelNames);
//        print_r($this->usageLevelNames);
    }

    private function getLevelsId(): void
    {
        $sql = <<<SQL
            SELECT
            	con.ID contact_id,
            	ASSIGNED_BY_ID responsible_id,
            	ucon.UF_CRM_1524141984 influence_level_id, -- Уровень влияния
            	ucon.UF_CRM_1551781165 usage_level_id -- Уровень использования К+ 
            FROM
            	b_crm_contact con
            	JOIN b_uts_crm_contact ucon ON con.ID = ucon.VALUE_ID
            WHERE ucon.UF_CRM_1551781165 < 1969 -- Не пользователь			
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            if (empty($el['usage_level_id']))
                continue;
            $this->result[$el['contact_id']] = [
                'responsible_id' => $el['responsible_id'],
                'influence_level_id' => $el['influence_level_id'],
                'usage_level_id' => $el['usage_level_id'],
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
        foreach ($this->result as &$el) {
            $el['usage_level'] = $this->usageLevelNames[$el['usage_level_id']];
            $el['influence_level'] = $this->influenceLevelNames[$el['influence_level_id']] ?? '';
            $el['responsible'] = $this->users[$el['responsible_id']] ?? '';

        }
    }
}