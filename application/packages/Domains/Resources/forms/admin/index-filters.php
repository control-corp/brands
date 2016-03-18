<?php

use Nomenclatures\Model;

$nomNotifiers = new Model\Notifiers();
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