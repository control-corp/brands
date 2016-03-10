<?php

use Nomenclatures\Model;

$nomTypes = new Model\Types();
$nomNotifiers = new Model\Notifiers();
$nomStatuses = new Model\Statuses();
$nomClasses = new Model\BrandClasses();
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
                'isArray' => 1,
                'class' => 'form-control selectpicker',
                'multiOptions' => $nomCountries->fetchCachedPairs(null, null, array('name' => 'asc'))
            )
        ),
        'typeId' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Тип на марката',
                'labelClass' => 'control-label',
                'required' => 1,
                'class' => 'form-control',
                'emptyOption' => 'Избери',
                'multiOptions' => $nomTypes->fetchCachedPairs()
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
        'classes' => array(
            'type' => 'select',
            'options' => array(
                'label' => 'Класове',
                'labelClass' => 'control-label',
                'required' => 1,
                'isArray' => 1,
                'class' => 'form-control',
                'multiOptions' => $nomClasses->fetchCachedPairs(),
                'attributes' => array(
                    'style' => 'height: 200px'
                )
            )
        ),
        'active' => array(
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Активност',
                'labelClass' => 'control-label',
            )
        ),
        'requestNum' => array(
            'type' => 'text',
            'options' => array(
                'required' => 1,
                'label' => 'Номер на заяваване',
                'labelClass' => 'control-label',
                'class' => 'form-control'
            )
        ),
        'requestDate' => array(
            'type' => 'datepicker',
            'options' => array(
                'format' => 'd.m.Y',
                'required' => 1,
                'label' => 'Дата на заявяване',
                'labelClass' => 'control-label',
                'class' => 'datepicker form-control'
            )
        ),
        'registerNum' => array(
            'type' => 'text',
            'options' => array(
                'label' => 'Номер на регистрация',
                'labelClass' => 'control-label',
                'class' => 'form-control'
            )
        ),
        'registerDate' => array(
            'type' => 'datepicker',
            'options' => array(
                'format' => 'd.m.Y',
                'label' => 'Дата на регистрация',
                'labelClass' => 'control-label',
                'class' => 'datepicker form-control'
            )
        ),
        'btnSave'  => ['type' => 'submit', 'options' => ['value' => 'Генериране', 'class' => 'btn btn-primary']],
        'btnBack'  => ['type' => 'submit', 'options' => ['value' => 'Назад', 'class' => 'btn btn-default']],
    )
);