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
                'required' => 1,
                'class' => 'form-control'
            )
        ),
        'countryId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Държава',
                'labelClass' => 'control-label',
                'required' => 1,
                'class' => 'form-control selectpicker',
                'emptyOption' => 'Избери',
                'multiOptions' => $nomCountries->fetchCachedPairs(null, null, array('name' => 'asc'))
            )
        ),
        'dateStart' => array(
            'type' => 'datepicker',
            'options' => array(
                'format' => 'd.m.Y',
                'label' => 'Начална дата',
                'required' => 1,
                'labelClass' => 'control-label',
                'class' => 'datepicker form-control'
            )
        ),
		'dateEnd' => array(
            'type' => 'datepicker',
            'options' => array(
                'format' => 'd.m.Y',
                'label' => 'Крайна дата',
                'required' => 1,
                'labelClass' => 'control-label',
                'class' => 'datepicker form-control'
            )
        ),
        'notifierId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Заявител',
                'labelClass' => 'control-label',
                'required' => 1,
                'class' => 'form-control',
                'emptyOption' => 'Избери',
                'multiOptions' => $nomNotifiers->fetchCachedPairs()
            )
        ),
        'active' => array(
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Активност',
                'labelClass' => 'control-label',
            )
        ),
        'description' => array(
            'type' => 'textarea',
            'options' => array(
                'label' => 'Информация',
                'labelClass' => 'control-label',
                'class' => 'form-control summernote',
                'attributes' => array(
                    'rows' => 5
                )
            )
        ),
        'price' => array(
            'type' => 'text',
            'options' => array(
                'label' => 'Цена',
                'labelClass' => 'control-label',
                'class' => 'form-control'
            )
        ),
        'btnSave'  => ['type' => 'submit', 'options' => ['value' => 'Запазване', 'class' => 'btn btn-primary']],
        'btnApply' => ['type' => 'submit', 'options' => ['value' => 'Прилагане', 'class' => 'btn btn-success']],
        'btnBack'  => ['type' => 'submit', 'options' => ['value' => 'Назад', 'class' => 'btn btn-default']],
    )
);