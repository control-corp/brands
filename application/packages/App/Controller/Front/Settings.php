<?php

namespace App\Controller\Front;

use App\Model\Table\Settings as SettingsModel;
use Micro\Application\Utils;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Controller;

class Settings extends Controller
{
    public function indexAction()
    {
        if ($this->request->isPost()) {

            $settingsModel = new SettingsModel();

            $post = $this->request->getPost();

            $post = Utils::arrayMapRecursive('trim', $post, \true);

            foreach ($post as $k => $v) {
                $settingsModel->update(['value' => $v], ['`key` = ?' => $k]);
            }

            SettingsModel::removeCache();

            return (new RedirectResponse(route()))->withFlash();
        }

        $this->view->assign('settings', SettingsModel::getSettings());
    }
}