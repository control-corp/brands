<?php

namespace Domains\Model;

use Micro\Model\DatabaseAbstract;
use Micro\Model\EntityInterface;

class Domains extends DatabaseAbstract
{
    protected $table = Table\Domains::class;

    protected $entity = Entity\Domain::class;

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

            if ($entity->getDateStart()) {
                $date = new \DateTime($entity->getDateStart());
                $entity->setDateStart($date->format('Y-m-d'));
            }

			if ($entity->getDateEnd()) {
                $date = new \DateTime($entity->getDateEnd());
                $entity->setDateEnd($date->format('Y-m-d'));
            }

            $result = parent::save($entity);

            $this->commit();

        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }
}