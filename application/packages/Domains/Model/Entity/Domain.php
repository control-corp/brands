<?php

namespace Domains\Model\Entity;

use Micro\Model\EntityAbstract;

class Domain extends EntityAbstract
{
    protected $id;
    protected $countryId;
    protected $name;
    protected $notifierId;
    protected $description;
    protected $dateStart;
    protected $dateEnd;
    protected $active = 1;
    protected $price;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCountryId()
    {
        return $this->countryId;
    }

    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName ($name)
    {
        $this->name = $name;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getNotifierId ()
    {
        return $this->notifierId;
    }

    public function setNotifierId ($notifierId)
    {
        $this->notifierId = $notifierId;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function setDescription ($description)
    {
        $this->description = $description;
    }

    public function getDateStart ()
    {
        return $this->dateStart;
    }

    public function setDateStart ($date)
    {
        $this->dateStart = $date;
    }
	
	public function getDateEnd ()
    {
        return $this->dateEnd;
    }

    public function setDateEnd ($date)
    {
        $this->dateEnd = $date;
    }

    public function getPrice ()
    {
        return $this->price;
    }

    public function setPrice ($price)
    {
        $this->price = $price;
    }

    public function getFormatedPrice($price = null, \Nomenclatures\Model\Entity\Currency $currency = null)
    {
        if ($price === null) {
            $price = $this->getPrice();
        }

        if (empty($price)) {
            return '';
        }

        $countriesTable = new \Nomenclatures\Model\Table\Countries();
        $currenciesModel = new \Nomenclatures\Model\Currencies();

        $defaultCurrency =  config('currency.default');

        $countryCurrencies = $countriesTable->getCountryCurrencies();

        $currencyId = isset($countryCurrencies[$this->getCountryId()]) && $countryCurrencies[$this->getCountryId()]
                      ? $countryCurrencies[$this->getCountryId()]
                      : $defaultCurrency;

        $currencySymbols = $currenciesModel->fetchCachedPairs(null, array('id', 'symbol'));
        $symbol = isset($currencySymbols[$currencyId]) && $currencySymbols[$currencyId] ? $currencySymbols[$currencyId] : '';

        if ($currency !== null) {

            $currencyRates = $currenciesModel->fetchCachedPairs(null, array('id', 'rate'));
            $rate = isset($currencyRates[$currencyId]) && $currencyRates[$currencyId] ? $currencyRates[$currencyId] : 1;

            $symbol = $currency['symbol'];
            $price  = $price * $currency['rate'] / $rate;

        }

        $price = round($price, 2);

        return $price . ' ' . $symbol;
    }

    public function getCurrencySymbol()
    {
        $countriesTable = new \Nomenclatures\Model\Table\Countries();
        $currenciesModel = new \Nomenclatures\Model\Currencies();

        $defaultCurrency =  config('currency.default');

        $countryCurrencies = $countriesTable->getCountryCurrencies();

        $currencyId = isset($countryCurrencies[$this->getCountryId()]) && $countryCurrencies[$this->getCountryId()]
                      ? $countryCurrencies[$this->getCountryId()]
                      : $defaultCurrency;

        $currencySymbols = $currenciesModel->fetchCachedPairs(null, array('id', 'symbol'));

        return isset($currencySymbols[$currencyId]) && $currencySymbols[$currencyId] ? $currencySymbols[$currencyId] : '';
    }
}