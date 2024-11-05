<?php

namespace App;

class ServingCompany extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            select cco.id
            	 -- ,cco.TITLE as 'name'
            	  ,ie.NAME as 'group'
            	  ,TRIM(CONCAT_WS(" ", ru.LAST_NAME, ru.NAME, ru.SECOND_NAME)) as 'responsible'
            	  ,TRIM(CONCAT_WS(" ", su.LAST_NAME, su.NAME, su.SECOND_NAME)) as 'supervisor'
            from b_crm_company cco
            	join b_uts_crm_company ucco
            		on cco.ID = ucco.VALUE_ID
            	inner join b_user_field_enum ufe
            		on ucco.UF_CRM_1524464429 = ufe.ID
            	   and ufe.USER_FIELD_ID = 182
            	   and ufe.ID = 68
            	left join (
            		select ie.ID,
            		       ie.NAME,
            			   iep.VALUE as 'SUPERVISOR'
            		  from b_iblock_element ie
            			join b_iblock_element_property iep
            		         on ie.ID = iep.IBLOCK_ELEMENT_ID
            				and iep.IBLOCK_PROPERTY_ID = 1731
            	      where ie.IBLOCK_ID = 67
            	          ) ie 
            	   on ucco.UF_CRM_1527063036 = ie.ID
            	left join b_user ru
            		on ru.ID = cco.ASSIGNED_BY_ID
            	left join b_user su
            		on su.ID = ie.SUPERVISOR
            where cco.COMPANY_TYPE != 4
        SQL;
        $result = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $el) {
            $this->result[$el['id']] = $el;
        }
//        print_r($this->result);
    }
}