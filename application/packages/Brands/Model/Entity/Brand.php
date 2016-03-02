<?php

namespace Brands\Model\Entity;

use Micro\Model\EntityAbstract;
use Brands\Model\Table\BrandsStatusesRel;

class Brand extends EntityAbstract
{
    protected $id;
    protected $countryId;
    protected $name;
    protected $typeId;
    protected $notifierId;
    protected $statusId;
    protected $classes;
    protected $description;
    protected $requestNum;
    protected $requestDate;
    protected $registerNum;
    protected $registerDate;
    protected $reNewDate;
    protected $statusDate;
    protected $statusNote;
    protected $active = 1;

    protected $price;

    protected $statusHistory;

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

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getTypeId ()
    {
        return $this->typeId;
    }

    public function setTypeId ($typeId)
    {
        $this->typeId = $typeId;
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

    public function getClasses ()
    {
        return $this->classes;
    }

    public function setClasses ($classes)
    {
        $this->classes = $classes;
    }

    public function getDescription ()
    {
        return $this->description;
    }

    public function setDescription ($description)
    {
        $this->description = $description;
    }

    public function getRequestNum ()
    {
        return $this->requestNum;
    }

    public function setRequestNum ($requestNum)
    {
        $this->requestNum = $requestNum;
    }

    public function getRequestDate ()
    {
        return $this->requestDate;
    }

    public function setRequestDate ($requestDate)
    {
        $this->requestDate = $requestDate;
    }

    public function getRegisterNum ()
    {
        return $this->registerNum;
    }

    public function setRegisterNum ($registerNum)
    {
        $this->registerNum = $registerNum;
    }

    public function getRegisterDate ()
    {
        return $this->registerDate;
    }

    public function setRegisterDate ($registerDate)
    {
        $this->registerDate = $registerDate;
    }

    public function getStatusDate ()
    {
        return $this->statusDate;
    }

    public function setStatusDate ($statusDate)
    {
        $this->statusDate = $statusDate;
    }

    public function getStatusNote ()
    {
        return $this->statusNote;
    }

    public function setStatusNote ($statusNote)
    {
        $this->statusNote = $statusNote;
    }

    public function getPrice ()
    {
        return $this->price;
    }

    public function setPrice ($price)
    {
        $this->price = $price;
    }

    public function getReNewDate ()
    {
        return $this->reNewDate;
    }

    public function setReNewDate ($reNewDate)
    {
        $this->reNewDate = $reNewDate;
    }

    public function getStatusHistory()
    {
        if ($this->statusHistory === null) {
            $rel = new BrandsStatusesRel();
            $this->statusHistory = $rel->getAdapter()->fetchAll($rel->select(true)->setIntegrityCheck(false)->where('brandId = ?', $this->getId())->order('date DESC'));
        }

        return $this->statusHistory;
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
            $path = \Brands\Model\Brands::getImagePath($this->id, $image);
            if (file_exists($path)) {
                $parts = explode('/', $path);
                $parts[count($parts) - 1] = 'thumbs/' . $parts[count($parts) - 1];
                $thumb = implode('/', $parts);
                if (!file_exists($thumb)) {
                    try {
                        $resizer = new \Micro\Image\Native($path);
                        $resizer->resizeAndFill(50, 150);
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
        $countrySymbols = $countriesTable->getCountrySymbols();

        $symbol = isset($countrySymbols[$this->getCountryId()]) ? $countrySymbols[$this->getCountryId()] : null;

        if (empty($symbol)) {
            $symbol = config('currency.defaultSymbol');
        }

        if ($currency !== null) {
            $symbol = $currency['symbol'];
            $price = $price * $currency['rate'];
        }

        $price = round($price, 2);

        return $price . ' ' . $symbol;
    }
}