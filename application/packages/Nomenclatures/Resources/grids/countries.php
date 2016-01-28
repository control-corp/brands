<?php

return array(
    'paginatorPlacement' => 'both',
    'paginatorAlways' => 0,
    'buttons' => array(
        'btnAdd' => array(
            'value' => 'Добавяне',
            'class' => 'btn btn-primary'
        ),
        'btnActivate' => array(
            'value' => 'Активиране',
            'class' => 'btn btn-success',
            'attributes' => array(
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'activate']),
                'data-confirm' => 'Сигурни ли сте, че искате да активирате избраните записи?'
            )
        ),
        'btnDeactivate' => array(
            'value' => 'Деактивиране',
            'class' => 'btn btn-warning',
            'attributes' => array(
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'deactivate']),
                'data-confirm' => 'Сигурни ли сте, че искате да деактивирате избраните записи?'
            )
        ),
        'btnDelete' => array(
            'value' => 'Изтриване',
            'class' => 'btn btn-danger',
            'attributes' => array(
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'delete']),
                'data-confirm' => 'Сигурни ли сте, че искате да изтриете избраните записи?'
            )
        )
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
    )
);