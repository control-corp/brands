<?php

namespace Nomenclatures\Controller;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\RedirectResponse;
use Micro\Form\Form;
use Nomenclatures\Model\Countries as CountriesModel;
use Micro\Database\Table\Row;

class Countries extends Controller
{
    public function index()
    {
        $model = new CountriesModel();

        return new View('countries/index', ['items' => $model->getItems()]);
    }

    public function add(Row $entity = \null)
    {
        $form = new Form(package_path('Nomenclatures', 'forms/countries-add.php'));

        if ($entity !== \null) {
            $form->populate($entity->toArray());
        }

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            if ($form->isValid($post)) {
                $model = new CountriesModel();
                if ($entity === \null) {
                    $entity = $post + ['language_id' => 2];
                } else {
                    $entity->setFromArray($post);
                }
                $model->save($entity);
                return (new RedirectResponse(route('default', ['action' => 'index'])))->withFlash(sprintf('Държавата е %s', ($entity ? 'редактирана' : 'добавена')));
            }
        }

        return new View('countries/add', ['form' => $form]);
    }

    public function edit()
    {
        $model = new CountriesModel();
        $item  = $model->getItem($this->request->getParam('id'));

        if ($item === \null) {
            throw new \Exception(sprintf('Държавата не е намерена'), 404);
        }

        return $this->add($item);
    }

    public function view()
    {
        $model = new CountriesModel();
        $item  = $model->getItem($this->request->getParam('id'));

        if ($item === \null) {
            throw new \Exception(sprintf('Държавата не е намерена'), 404);
        }

        return new View('countries/view', ['item' => $item]);
    }

    public function delete()
    {
        $model = new CountriesModel();
        $model->delete(array('id = ?' => (int) $this->request->getParam('id')));

        return (new RedirectResponse(route('default', ['action' => 'index', 'id' => \null])))->withFlash('Държавата е изтрита');
    }
}