<?php

namespace Designs\Model\Entity;

use Micro\Model\EntityAbstract;

class Design extends EntityAbstract
{
    protected $id;
    protected $countryId;
    protected $name;
    protected $notifierId;
    protected $statusId;
    protected $description;
    protected $date;
    protected $active = 1;
    protected $price;
    protected $image;

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

    public function getStatusId ()
    {
        return $this->statusId;
    }

    public function setStatusId ($statusId)
    {
        $this->statusId = $statusId;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function setDescription ($description)
    {
        $this->description = $description;
    }

    public function getDate ()
    {
        return $this->date;
    }

    public function setDate ($date)
    {
        $this->date = $date;
    }

    public function getPrice ()
    {
        return $this->price;
    }

    public function setPrice ($price)
    {
        $this->price = $price;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getThumb()
    {
        $image = $this->getImage();

        $thumb = null;

        if ($image) {
            $path = \Designs\Model\Designs::getImagePath($this->id, $image);
            if (file_exists($path)) {
                $parts = explode('/', $path);
                $parts[count($parts) - 1] = 'thumbs/' . $parts[count($parts) - 1];
                $thumb = implode('/', $parts);
                if (!file_exists($thumb)) {
                    try {
                        $resizer = new \Micro\Image\Native($path);
                        $resizer->resizeAndFill(config('thumb.width', 150), config('thumb.height', 90));
                        $resizer->save($thumb);
                    } catch (\Exception $e) {
                        $thumb = null;
                    }
                }
            }
        }

        return $thumb;
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