<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Model\SystemUpdateModel;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use LogicException;

class SystemUpdateController extends AdminController
{
  public function index(Request $request): string
  {
    $release_list = SystemUpdateModel::getReleaseInfo();
    $this->set('release_list', $release_list);
    $this->set('repo_site_url', SystemUpdateModel::$releases_url);
    $this->set('now_version', SystemUpdateModel::getVersion());
    return 'admin/system_update/index.twig';
  }

  public function update(Request $request): string
  {
    // check sig
    if(!$request->isValidSig()){
      $this->setWarnMessage("request failed. please retry."); // TODO i18n
      $this->redirect($request, Html::url($request, ['controller'=>'ServerUpdate', 'action'=>'index']));
    }

    // check request
    $request_version = $request->get('version');
    if(is_null($request_version)){
      $this->setWarnMessage("request failed. missing version."); // TODO i18n
      $this->redirect($request, Html::url($request, ['controller'=>'ServerUpdate', 'action'=>'index']));
    }

    $release_list = SystemUpdateModel::getReleaseInfo();
    // get request version
    $release = SystemUpdateModel::findByVersionFromReleaseList($release_list, $request_version);

    $zip_url = SystemUpdateModel::getZipDownloadUrl($release);

    SystemUpdateModel::updateSystemByUrl($zip_url);

    // add flash
    $this->setInfoMessage("updated");

    // redirect to index
    $this->redirect($request, Html::url($request, ['controller'=>'ServerUpdate', 'action'=>'index']));

    throw new LogicException("must be redirect");
  }

}

