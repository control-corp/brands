<?php

use Nomenclatures\Model;

$nomNotifiers = new Model\Notifiers();
$nomStatuses = new Model\DesignStatuses();
$nomCountries = new Model\Countries();

return array(
    'elements' => array(
        'name' => array(
            'type' => 'text',
            'options' => array(
                'label' => 'Име',
                'labelClass' => 'control-label',
                'class' => 'form-control',
                'belongsTo' => 'filters',
            )
        ),
        'countryId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Държава',
                'labelClass' => 'control-label',
                'isArray' => 1,
                'class' => 'form-control selectpicker',
                'belongsTo' => 'filters',
                'multiOptions' => $nomCountries->fetchCachedPairs(null, null, array('name' => 'asc'))
            )
        ),
        'statusId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Статус',
                'labelClass' => 'control-label',
                'class' => 'form-control',
                'emptyOption' => 'Избери',
                'belongsTo' => 'filters',
                'multiOptions' => $nomStatuses->fetchCachedPairs()
            )
        ),
        'notifierId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Заявител',
                'labelClass' => 'control-label',
                'class' => 'form-control',
                'emptyOption' => 'Избери',
                'belongsTo' => 'filters',
                'multiOptions' => $nomNotifiers->fetchCachedPairs()
            )
        ),
    )
);