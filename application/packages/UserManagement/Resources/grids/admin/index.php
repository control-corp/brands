<?php

return array(
    'paginatorPlacement' => 'both',
    'paginatorAlways' => 0,
    'buttons' => array(
        'btnAdd' => array(
            'value' => 'Добавяне',
            'class' => 'btn btn-primary'
        ),
    ),
    'columns' => array(
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
        'username' => array(
            'type' => 'href',
            'options' => array(
                'sourceField' => 'username',
                'sortable' => 1,
                'title' => 'Име',
                'params' => array(
                    'action' => 'edit',
                    'id' => ':id'
                )
            )
        ),
    )
);