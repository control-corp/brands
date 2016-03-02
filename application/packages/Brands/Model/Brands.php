<?php

namespace Brands\Model;

use Micro\Model\DatabaseAbstract;
use Micro\Model\EntityInterface;

class Brands extends DatabaseAbstract
{
    protected $table = Table\Brands::class;

    protected $entity = Entity\Brand::class;

    /**
     * (non-PHPdoc)
     * @see \Micro\Model\DatabaseAbstract::save()
     */
    public function save(EntityInterface $entity)
    {
        try {

            $this->beginTransaction();

            $test = strip_tags($entity->getDescription());

            if (empty($test)) {
                $entity->setDescription(null);
            }

            if ($entity->getRegisterDate()) {
                $date = new \DateTime($entity->getRegisterDate());
                $entity->setRegisterDate($date->format('Y-m-d'));
                $entity->setReNewDate($date->modify('+10 years')->format('Y-m-d'));
            }

            if ($entity->getRequestDate()) {
                $date = new \DateTime($entity->getRequestDate());
                $entity->setRequestDate($date->format('Y-m-d'));
            }

            if ($entity->getStatusDate()) {
                $date = new \DateTime($entity->getStatusDate());
                $entity->setStatusDate($date->format('Y-m-d'));
            }

            if ($entity->getReNewDate()) {
                $date = new \DateTime($entity->getReNewDate());
                $entity->setReNewDate($date->format('Y-m-d'));
            }

            $result = parent::save($entity);

            $this->saveStatus($entity);
            $this->saveImage($entity);

            /**
             * @todo FIX me
             */
            $this->getTable()->getAdapter()->query('
                UPDATE NomCountries
                SET countBrands = (SELECT COUNT(1) FROM Brands WHERE Brands.countryId = NomCountries.id)
            ');

            $this->commit();

        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    protected function saveStatus(EntityInterface $entity)
    {
        if (!$entity->getStatusId() || !$entity->getStatusDate()) {
            return;
        }

        $rel = new Table\BrandsStatusesRel();

        $row = $rel->fetchRow(array(
            'brandId = ?' => $entity->getId(),
            'statusId = ?' => $entity->getStatusId(),
            'date = ?' => $entity->getStatusDate())
        );

        try {
            if ($row === \null) {
                $row = $rel->createRow(array(
                    'brandId' => $entity->getId(),
                    'statusId' => $entity->getStatusId(),
                    'date' => $entity->getStatusDate()
                ));
            }
            $row->price = $entity->getPrice();
            $row->note = $entity->getStatusNote();
            $row->save();
        } catch (\Exception $e) {

        }

        $this->fixLastStatus((int) $entity->getId());
    }

    public function fixLastStatus($brandId)
    {
        $rel = new Table\BrandsStatusesRel();

        $row = $rel->fetchRow(array('brandId = ?' => $brandId), 'id DESC');

        if ($row !== null) {
            $data = array(
                'statusId' => $row['statusId'],
                'statusDate' => $row['date'],
                'statusNote' => $row['note'],
                'price' => $row['price'],
            );
        } else {
            $data = array(
                'statusId' => \null,
                'statusDate' => \null,
                'statusNote' => \null,
                'price' => \null,
            );
        }

        $this->getTable()->update($data, array('id = ?' => $brandId));
    }

    protected function saveImage(EntityInterface $entity)
    {
        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === 0) {
                $name     = $_FILES['image']['name'];
                $tmp_name = $_FILES['image']['tmp_name'];
                @unlink(static::getImagePath($entity->getId(), $name, true));
                if (!copy($tmp_name, static::getImagePath($entity->getId(), $name))) {
                    throw new \Exception('Файлът не може да се запише', 500);
                }
                $this->getTable()->update(array('image' => $name), array('id = ?' => $entity->getId()));
            }
        }
    }

    public function applyPaginatorFilters(array $params)
    {
        $classes = null;

        if (isset($params['classes']) && $params['classes']) {
            $classes = $params['classes'];
            unset($params['classes']);
        }

        parent::applyPaginatorFilters($params);

        if ($classes !== null) {
            $this->addWhere(new \Micro\Database\Expr('FIND_IN_SET(' . $classes . ', classes)'));
        }
    }

    public static function getImagePath($id, $image, $thumb = false)
    {
        return public_path('uploads/brands/' . ($thumb ? 'thumbs/' : '') . $id . '.' . pathinfo($image, PATHINFO_EXTENSION));
    }

    public function multipleInsert(array $data)
    {
        foreach ($data['countryId'] as $countryId) {
            $entity = $this->createEntity();
            $statusId = null;
            $statusDate = null;
            if ($data['registerDate']) {
                $statusId = 7; // публикувана
                $statusDate = $data['registerDate'];
            }
            $entity->setFromArray(
                array('countryId' => $countryId, 'statusId' => $statusId, 'statusDate' => $statusDate)
                +
                $data
            );
            $entity->setClasses(implode(',', $entity->getClasses()));
            $this->save($entity);
        }
    }
}