<?php

namespace Brands\Controller\Admin;

use Micro\Form\Form;
use Micro\Grid\Grid;
use Micro\Http\Response;

use Nomenclatures\Model\Continents;
use Nomenclatures\Model\Types;
use Nomenclatures\Model\Countries;
use Brands\Model\Brands;
use Nomenclatures\Model\Statuses;
use Micro\Application\Controller\Crud;
use Micro\Database\Expr;
use Micro\Http\Response\JsonResponse;

class Reports extends Crud
{
    protected $scope = 'admin';

    public function allBrandsAction()
    {
        $filters = parent::handleFilters();

        if ($filters instanceof Response) {
            return $filters;
        }

        $ipp = max($this->ipp, $this->request->getParam('ipp', $this->ipp));
        $page = max(1, $this->request->getParam('page', 1));

        $selectedBrands = isset($filters['brands']) ? json_decode($filters['brands'], \true) : [];

        $form = new Form(package_path('Brands', 'Resources/forms/admin/reports-all-brands-filters.php'));

        $form->populate($filters);

        $brandsModel = new Brands();

        if (!empty($selectedBrands)) {
            $brandsModel->addWhere('name', $selectedBrands);
        }

        $brandsModel->addOrder('name');

        $brandsSelect = $brandsModel->getJoinSelect();

        $brandsSelect->joinInner('NomCountries', 'NomCountries.id = Brands.countryId', []);

        if (isset($filters['countryId']) && !empty($filters['countryId'])) {
            $brandsSelect->where('Brands.countryId IN(?)', $filters['countryId']);
        }

        if (isset($filters['continentId']) && !empty($filters['continentId'])) {
            $brandsSelect->where('NomCountries.continentId IN(?)', $filters['continentId']);
        }

        $grid = new Grid($brandsModel, package_path('Brands', 'Resources/grids/admin/reports/all-brands.php'));

        $grid->getRenderer()->setView($this->view);

        $grid->setIpp($ipp);
        $grid->setPageNumber($page);

        $nomStatuses = new \Nomenclatures\Model\Statuses();
        $this->view->assign('nomStatuses', $nomStatuses->fetchCachedPairs());

        return ['form' => $form, 'selectedBrands' => $selectedBrands, 'grid' => $grid];
    }

    public function ajaxGetCategoriesAction()
    {
        $ids = explode(',', $this->request->getParam('continentId'));
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return new JsonResponse([]);
        }

        $cModel = new \Nomenclatures\Model\Countries();

        $res = [];

        foreach ($cModel->fetchPairs(['continentId' => $ids], \null, ['name' => 'asc']) as $k => $v) {
            $res[] = [
                'key' => $k,
                'value' => $v
            ];
        }

        return new JsonResponse($res);
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
            return ['form' => $form, 'brands' => $brands];
        }

        $nomContinents = new Continents();
        $continents = $nomContinents->fetchCachedPairs(array('active' => 1), null, array('id' => 'ASC'));

        $nomTypes = new Types();
        $types = $nomTypes->fetchCachedPairs(['active' => 1]);

        $nomCountries = new Countries();
        $nomCountries->addWhere('active', '1');
        $nomCountries->addOrder('name', 'ASC');
        $countriesRows = $nomCountries->getItems();

        $countries = array();
        $populations = array();

        foreach ($countriesRows as $countryRow) {

            if (empty($countryRow['continentId'])) {
                continue;
            }

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

        return [
            'form' => $form,
            'continents' => $continents,
            'populations' => $populations,
            'types' => $types,
            'countries' => $countries,
            'brands' => $brands,
            'brandImages' => $brandImages,
            'statuses' => $statuses,
            'statusesColors' => $statusesColors,
        ];
    }

    public function ajaxGetBrandsAction()
    {
        $response = array();

        $query = $this->request->getParam('query');

        if (mb_strlen($query, 'UTF-8') > 2) {
            $brandsModel = new Brands();
            $brandsModel->addWhere('name', $query . '%');
            $brandsModel->getJoinSelect()->group('name')->order(new Expr('NULL'));
            foreach ($brandsModel->getItems() as $item) {
                $response[] = array('id' => $item['name'], 'name' => $item['name']);
            }
        }

        return new Response\JsonResponse($response);
    }

    public function exportAction()
    {
        $brands = $this->brandsAction();

        if ($brands instanceof Response || empty($brands['brands'])) {
            return new Response\HtmlResponse();
        }

        $phpExcel = new \PHPExcel();
        $phpExcel->setActiveSheetIndex(0);

        $sheet = $phpExcel->getActiveSheet();

        $rowIndex = '1';
        $chars = range('A', 'Z');

        $countTypes = count($brands['types']);
        $count = $countTypes * 3 + 1;

        foreach ($brands['continents'] as $continentId => $continent) {

            $cellIndex = 'A';

            $sheet->setCellValue($cellIndex . $rowIndex, $continent);
            $sheet->mergeCells($cellIndex . $rowIndex . ':' . $chars[$count - 1] . $rowIndex);
            $rowIndex++;

            $sheet->setCellValue($cellIndex . $rowIndex, 'Държава');

            $cellIndex++;
            $sheet->setCellValue($cellIndex . $rowIndex, 'Статус');
            $mergeIndex = $cellIndex . $rowIndex . ':' . $chars[array_search($cellIndex, $chars) + $countTypes - 1] . $rowIndex;
            $sheet->mergeCells($mergeIndex);

            $cellIndex = $chars[array_search($cellIndex, $chars) + $countTypes - 1];
            $cellIndex++;
            $sheet->setCellValue($cellIndex . $rowIndex, 'Коментар');
            $mergeIndex = $cellIndex . $rowIndex . ':' . $chars[array_search($cellIndex, $chars) + $countTypes - 1] . $rowIndex;
            $sheet->mergeCells($mergeIndex);

            $cellIndex = $chars[array_search($cellIndex, $chars) + $countTypes - 1];
            $cellIndex++;
            $sheet->setCellValue($cellIndex . $rowIndex, 'Предприети действия');
            $mergeIndex = $cellIndex . $rowIndex . ':' . $chars[array_search($cellIndex, $chars) + $countTypes - 1] . $rowIndex;
            $sheet->mergeCells($mergeIndex);

            $rowIndex++;

            $cellIndex = 'A';
            $sheet->setCellValue($cellIndex . $rowIndex, "Общо държави:\nОбщо население:");
            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setWrapText(true);
            $sheet->getColumnDimension('A')->setAutoSize(true);

            $cellIndex++;
            foreach (range(1, 3) as $i) {
                foreach ($brands['types'] as $type) {
                    $sheet->setCellValue($cellIndex . $rowIndex, $type);
                    $sheet->getColumnDimension($cellIndex)->setAutoSize(true);
                    $cellIndex++;
                }
            }

            $rowIndex++;
        }

        $writer = \PHPExcel_IOFactory::createWriter ($phpExcel, 'Excel5');
        $writer->save('data/brand.xls');

        die;
    }
}