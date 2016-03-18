<?php

namespace Designs\Model;

use Micro\Model\DatabaseAbstract;
use Micro\Model\EntityInterface;

class Designs extends DatabaseAbstract
{
    protected $table = Table\Designs::class;

    protected $entity = Entity\Design::class;

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

            if ($entity->getDate()) {
                $date = new \DateTime($entity->getDate());
                $entity->setDate($date->format('Y-m-d'));
            }

            $result = parent::save($entity);

            $this->saveImage($entity);

            $this->commit();

        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
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

    public static function getImagePath($id, $image, $thumb = false)
    {
        return public_path('uploads/designs/' . ($thumb ? 'thumbs/' : '') . $id . '.' . pathinfo($image, PATHINFO_EXTENSION));
    }
}