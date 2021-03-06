<?php

if (!function_exists('toDate')) {
    function toDate($value, $format) {
        if (empty($value)) {
            return null;
        }
        $value = new \DateTime($value);
        return $value->format($format);
    }
}

return array(
    'paginatorPlacement' => 'both',
    'buttons' => [
        'btnAdd' => [
            'value' => 'Добавяне',
            'class' => 'btn btn-primary',
            'resources' => array(
                'Domains\Controller\Admin\Index@add',
            ),
        ],
        'btnActivate' => [
            'value' => 'Активиране',
            'class' => 'btn btn-success',
            'attributes' => [
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'activate']),
                'data-confirm' => 'Сигурни ли сте, че искате да активирате избраните записи?'
            ],
            'resources' => array(
                'Domains\Controller\Admin\Index@activate',
            ),
        ],
        'btnDeactivate' => [
            'value' => 'Деактивиране',
            'class' => 'btn btn-warning',
            'attributes' => [
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'deactivate']),
                'data-confirm' => 'Сигурни ли сте, че искате да деактивирате избраните записи?'
            ],
            'resources' => array(
                'Domains\Controller\Admin\Index@deactivate',
            ),
        ],
        'btnDelete' => [
            'value' => 'Изтриване',
            'class' => 'btn btn-danger',
            'attributes' => [
                'data-rel' => 'ids[]',
                'data-action' => app('router')->assemble(\null, ['action' => 'delete']),
                'data-confirm' => 'Сигурни ли сте, че искате да изтриете избраните записи?'
            ],
            'resources' => array(
                'Domains\Controller\Admin\Index@delete',
            ),
        ]
    ],
    'columns' => array(
        'ids' => array(
            'type' => 'checkbox',
            'options' => array(
                'sourceField' => 'id',
                'checkAll' => 1,
                'class' => 'text-center',
                'headClass' => 'text-center',
                'headStyle' => 'width: 3%',
                'resources' => array(
                    'Domains\Controller\Admin\Index@add',
                    'Domains\Controller\Admin\Index@activate',
                    'Domains\Controller\Admin\Index@deactivate',
                    'Domains\Controller\Admin\Index@delete',
                ),
            )
        ),
        'name' => array(
            'options' => array(
                'sourceField' => 'name',
                'sortable' => 1,
                'title'  => 'Име',
                'viewScript' => 'admin/index/grid-edit',
            )
        ),
        'countryId' => array(
            'type' => 'pairs',
            'options' => array(
                'sourceField' => 'countryId',
                'title' => 'Държава',
                'callable' => array(new Nomenclatures\Model\Countries(), 'fetchCachedPairs'),
                'params' => [null, null, ['name' => 'asc']]
            )
        ),
        'dateStart' => array(
            'options' => array(
                'sourceField' => 'dateStart',
                'title' => 'Начална дата',
                'sortable' => 1,
                'filter' => array(
                    'callback' => 'toDate',
                    'params'   => array('format' => 'd.m.Y')
                )
            )
        ),
        'dateEnd' => array(
            'options' => array(
                'sourceField' => 'dateEnd',
                'title' => 'Крайна дата',
                'sortable' => 1,
                'filter' => array(
                    'callback' => 'toDate',
                    'params'   => array('format' => 'd.m.Y')
                )
            )
        ),
        'notifierId' => array(
            'type' => 'pairs',
            'options' => array(
                'sourceField' => 'notifierId',
                'title' => 'Заявител',
                'sortable' => 1,
                'callable' => array(new Nomenclatures\Model\Notifiers(), 'fetchCachedPairs')
            )
        ),
        'price' => array(
            'options' => array(
                'sourceField' => 'formatedPrice',
                'title' => 'Цена',
            )
        ),
        'active' => array(
            'type' => 'boolean',
            'options' => array(
                'sourceField' => 'active',
                'title' => 'Активност',
                'class' => 'text-center',
                'true' => '<span class="fa fa-check"></span>',
                'false' => '<span class="fa fa-ban"></span>',
                'style' => 'width: 5%'
            )
        ),
        'delete' => array(
            'type' => 'href',
            'options' => array(
                'text'   => ' ',
                'class'    => 'text-center',
                'hrefClass' => 'remove glyphicon glyphicon-trash',
                'style' => 'width: 5%',
                'reset'  => 0,
                'params' => array(
                    'action' => 'delete',
                    'id' => ':id'
                ),
                'resources' => array(
                    'Domains\Controller\Admin\Index@delete',
                ),
            )
        ),
    )
);