<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class Countries extends TableAbstract
{
    protected $_name = 'NomCountries';

    public function getCountryCurrencies()
    {
        $cache = app('cache');

        if ($cache === \null || ($rows = $cache->load('NomCountryCurrencyCurrencies')) === \false) {

            $rows = $this->getAdapter()->fetchPairs(
                $this->select(true)
                     ->setIntegrityCheck(false)
                     ->reset('columns')->columns(array('NomCountries.id', 'NomCountries.currencyId'))
            );

            if ($cache instanceof \Micro\Cache\Core) {
                $cache->save($rows, 'NomCountryCurrencyCurrencies', array('Nomenclatures_Model_Countries'));
            }
        }

        return $rows;
    }
}