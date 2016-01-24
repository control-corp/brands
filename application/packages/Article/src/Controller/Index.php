<?php

namespace Article\Controller;

use Micro\Application\Controller;
use Micro\Http\Response\RedirectResponse;
use Article\Model\Articles;
use Micro\Form\Form;
use Article\Model\Entity\Article;

class Index extends Controller
{
    public function index()
    {
        $model = new Articles();

        return ['items' => $model->getItems()];
    }

    public function add()
    {
        $form = new Form(package_path('Article', 'forms/add.php'));

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if ($form->isValid($post)) {
                $model = new Articles();
                $model->save($post + ['language_id' => 2]);
                return (new RedirectResponse(route('article.list')))->withFlash('Артикулът е добавен');
            }
        }

        return ['form' => $form];
    }

    public function detail()
    {
        $model = new Articles();

        $id    = $this->request->getParam('id');

        $item  = $model->getItem($id);

        if ($item === \null) {
            throw new \Exception(sprintf('Article "%s" not found', $id), 404);
        }

        return ['item' => $item];
    }

    public function delete()
    {
        $model = new Articles();

        $id = $this->request->getParam('id');

        $model->delete(array('id = ?' => (int) $id));

        return (new RedirectResponse(route('article.list')))->withFlash('Article ' . $id . ' is deleted');
    }
}