<?php

namespace Micro\Model;

interface ModelInterface extends \Countable
{
    public function getIdentifier();

    public function createEntity();

    public function find();

    public function addOrder($field, $direction = \null);

    public function addJoinCondition($field, $value);

    public function addFilters(array $params);

    public function getItems($offset = \null, $itemCountPerPage = \null);

    public function getItem();

    public function save(EntityAbstract $entity);

    public function delete(EntityAbstract $entity);
}