<?php

namespace Brands\Controller\Admin;

use Micro\Form\Form;
use Micro\Http\Response;

use Nomenclatures\Model\Continents;
use Nomenclatures\Model\Types;
use Nomenclatures\Model\Countries;
use Brands\Model\Brands;
use Nomenclatures\Model\Statuses;
use Micro\Application\Controller\Crud;

class Reports extends Crud
{
    protected $scope = 'admin';

    public function indexAction()
    {

    }

    public function brandsAction()
    {
        $filters = parent::handleFilters();

        if ($filters instanceof Response) {
            return $filters;
        }

        $form = new Form(package_path('Brands', 'Resources/forms/admin/reports-brands-filters.php'));

        $form->populate($filters);

        $brands = array();
        $brandImages = array();

        if (isset($filters['brandId'])) {

            $brandsModel = new Brands();
            $brandsModel->addWhere('name', $filters['brandId']);

            if (isset($filters['date']) && $filters['date']) {
                try {
                    $date = new \DateTime($filters['date']);
                    $statusDate = $date->format('Y-m-d');
                    //$brandsModel->addWhere(new Expr('statusDate <= "' . $brandsModel->getAdapter()->quote($statusDate) . '"'));
                } catch (\Exception $e) {

                }
            }

            $brandsRows = $brandsModel->getItems();

            foreach ($brandsRows as $brandRow) {

                if (!isset($brands[$brandRow['countryId']])) {
                    $brands[$brandRow['countryId']] = array();
                }

                $brands[$brandRow['countryId']][$brandRow['typeId']] = $brandRow;

                if (!isset($brandImages[$brandRow['typeId']])) {
                    if ($brandRow->getThumb()) {
                        $brandImages[$brandRow['typeId']] = array(
                            'path'  => $brandRow->getThumb(),
                            'image' => 'uploads/brands/thumbs/' . $brandRow->getId() . '.' . pathinfo($brandRow->getImage(), PATHINFO_EXTENSION)
                        );
                    }
                }
            }
        }

        if (empty($brands)) {
            return $this->view->addData([
                'form'   => $form,
                'brands' => $brands
            ]);
        }

        $nomContinents = new Continents();
        $continents = $nomContinents->fetchCachedPairs(array('active' => 1), null, array('id' => 'ASC'));

        $nomTypes = new Types();
        $types = $nomTypes->fetchCachedPairs();

        $nomCountries = new Countries();
        $nomCountries->addOrder('name', 'ASC');
        $countriesRows = $nomCountries->getItems();

        $countries = array();
        $populations = array();

        foreach ($countriesRows as $countryRow) {

            /**
             * Създаване на списъци от държави за континент
             */
            if (!isset($countries[$countryRow['continentId']])) {
                $countries[$countryRow['continentId']] = array();
            }

            $countries[$countryRow['continentId']][$countryRow['id']] = $countryRow;

            /**
             * Изчисляване на популацията за континент
             */
            if (!isset($populations[$countryRow['continentId']])) {
                $populations[$countryRow['continentId']] = 0;
            }

            $populations[$countryRow['continentId']] += $countryRow['population'];
        }

        $nomStatus = new Statuses();
        $statuses = $nomStatus->fetchCachedPairs();
        $nomStatus->resetSelect(true);
        $statusesColors = $nomStatus->fetchCachedPairs(null, array('id', 'color'));

        return $this->view->addData([
            'form' => $form,
            'continents' => $continents,
            'populations' => $populations,
            'types' => $types,
            'countries' => $countries,
            'brands' => $brands,
            'brandImages' => $brandImages,
            'statuses' => $statuses,
            'statusesColors' => $statusesColors,
        ]);
    }

    public function ajaxGetBrandsAction()
    {
        $response = array();

        $query = $this->request->getParam('query');

        if (mb_strlen($query, 'UTF-8') > 2) {
            $brandsModel = new Brands();
            $brandsModel->addWhere('name', $query . '%');
            $brandsModel->getJoinSelect()->group('name');
            foreach ($brandsModel->getItems() as $item) {
                $response[] = array('id' => $item['id'], 'name' => $item['name']);
            }
        }

        return new Response\JsonResponse($response);
    }
}