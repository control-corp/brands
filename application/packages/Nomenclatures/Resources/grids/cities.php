<?php

$config = include __DIR__ . '/countries.php';

$config['columns'] = array(
    'ids' => array(
        'type' => 'checkbox',
        'options' => array(
            'sourceField' => 'id',
            'checkAll' => 1,
            'class' => 'text-center',
            'headClass' => 'text-center',
            'headStyle' => 'width: 3%'
        )
    ),
    'id' => array(
        'options' => array(
            'title' => '#',
            'sourceField' => 'id',
            'headStyle' => 'width: 5%'
        )
    ),
    'name' => array(
        'type' => 'href',
        'options' => array(
            'sourceField' => 'name',
            'sortable' => 1,
            'title' => 'Име',
            'params' => array(
                'action' => 'edit',
                'id' => ':id'
            )
        )
    ),
    'country_id' => [
        'type' => 'pairs',
        'options' => [
            'sourceField' => 'country_id',
            'title' => 'Държава',
            'headStyle' => 'width: 15%',
            'callable' => array(new \Nomenclatures\Model\Countries(), 'fetchPairs')
        ]
    ],
    'active' => array(
        'type' => 'boolean',
        'options' => array(
            'sourceField' => 'active',
            'headStyle' => 'width: 5%',
            'title' => 'Активност',
            'class' => 'text-center',
            'true' => '<span class="fa fa-check"></span>',
            'false' => '<span class="fa fa-ban"></span>',
        )
    ),
    'delete' => array(
        'type' => 'href',
        'options' => array(
            'text' => ' ',
            'class' => 'text-center',
            'headStyle' => 'width: 5%',
            'hrefClass' => 'remove glyphicon glyphicon-trash',
            'reset' => 0,
            'params' => array(
                'action' => 'delete',
                'id' => ':id'
            )
        )
    )
);

return $config;