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
        'btnWizzard' => [
            'value' => 'Генериране на марки',
            'class' => 'btn btn-default',
            'resources' => array(
                'Brands\Controller\Admin\Index@wizzard',
            ),
        ],
        'btnAdd' => [
            'value' => 'Добавяне',
            'class' => 'btn btn-primary',
            'resources' => array(
                'Brands\Controller\Admin\Index@add',
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
                'Brands\Controller\Admin\Index@activate',
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
                'Brands\Controller\Admin\Index@deactivate',
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
                'Brands\Controller\Admin\Index@delete',
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
                    'Brands\Controller\Admin\Index@add',
                    'Brands\Controller\Admin\Index@activate',
                    'Brands\Controller\Admin\Index@deactivate',
                    'Brands\Controller\Admin\Index@delete',
                ),
            )
        ),
        'image' => array(
            'options' => array(
                'sourceField' => 'id',
                'viewScript' => 'admin/index/grid-image'
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
        'typeId' => array(
            'type' => 'pairs',
            'options' => array(
                'sourceField' => 'typeId',
                'title' => 'Тип на марката',
                'sortable' => 1,
                'callable' => array(new Nomenclatures\Model\Types(), 'fetchCachedPairs')
            )
        ),
        'requestNum' => array(
            'options' => array(
                'sourceField' => 'requestNum',
                'title' => 'Номер на заяваване',
                'sortable' => 1,
            )
        ),
        'requestDate' => array(
            'options' => array(
                'sourceField' => 'requestDate',
                'title' => 'Дата на заявяване',
                'sortable' => 1,
                'filter' => array(
                    'callback' => 'toDate',
                    'params'   => array('format' => 'd.m.Y')
                )
            )
        ),
        'registerNum' => array(
            'options' => array(
                'sourceField' => 'registerNum',
                'title' => 'Номер на регистрация',
                'sortable' => 1,
            )
        ),
        'registerDate' => array(
            'options' => array(
                'sourceField' => 'registerDate',
                'title' => 'Дата на регистрация',
                'sortable' => 1,
                'filter' => array(
                    'callback' => 'toDate',
                    'params'   => array('format' => 'd.m.Y')
                )
            )
        ),
        'classes' => array(
            'options' => array(
                'sourceField' => 'classes',
                'title' => 'Класове',
                'viewScript' => 'admin/index/grid-classes'
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
        'statusId' => array(
            'options' => array(
                'sourceField' => 'statusId',
                'title' => 'Статус на марката',
                'viewScript' => 'admin/index/grid-status'
            )
        ),
        'price' => array(
            'options' => array(
                'sourceField' => 'price',
                'title' => 'Цена',
                'viewScript' => 'admin/index/grid-price'
            )
        ),
        'reNewDate' => array(
            'options' => array(
                'sourceField' => 'reNewDate',
                'title' => 'Дата на подновяване',
                'sortable' => 1,
                'viewScript' => 'admin/index/grid-renew'
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
            )
        ),
        'delete' => array(
            'type' => 'href',
            'options' => array(
                'text'   => ' ',
                'class'    => 'text-center',
                'hrefClass' => 'remove glyphicon glyphicon-trash',
                'reset'  => 0,
                'params' => array(
                    'action' => 'delete',
                    'id' => ':id'
                ),
                'resources' => array(
                    'Brands\Controller\Admin\Index@delete',
                ),
            )
        ),
    )
);