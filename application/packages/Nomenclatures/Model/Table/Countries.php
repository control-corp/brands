<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class Countries extends TableAbstract
{
    protected $_name = 'NomCountries';

    public function getCountrySymbols()
    {
        $cache = app('cache');

        if ($cache === \null || ($rows = $cache->load('NomCountryCurrencySymbols')) === \false) {

            $rows = $this->getAdapter()->fetchPairs(
                $this->select(true)
                     ->setIntegrityCheck(false)
                     ->joinLeft('NomCurrencies', 'NomCurrencies.id = NomCountries.currencyId', array())
                     ->reset('columns')->columns(array('NomCountries.id', 'NomCurrencies.symbol'))
            );

            if ($cache instanceof \Micro\Cache\Core) {
                $cache->save($rows, 'NomCountryCurrencySymbols', array('Nomenclatures_Model_Countries', 'Nomenclatures_Model_Currencies'));
            }
        }

        return $rows;
    }
}