<?php

namespace Article;

use Micro\Application\Controller;
use Article\Model\Article;

class Index extends Controller
{
    public function index()
    {
        $model = new Article();

        return ['items' => $model->fetchAll()];
    }

    public function detail()
    {
        $model = new Article();

        $id    = $this->request->getParam('id');

        $item  = $model->find($id)->current();

        if ($item === \null) {
            throw new \Exception(sprintf('Article "%s" not found', $id), 404);
        }

        return ['item' => $item];
    }
}