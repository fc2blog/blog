<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Model\SystemUpdateModel;
use Fc2blog\Web\Request;

class SystemUpdateController extends AdminController
{
  public function index(Request $request): string
  {
    $release_list = SystemUpdateModel::getReleaseInfo();
    $this->set('release_list', $release_list);

    // TODO get this system version

    return 'admin/system_update/index.twig';
  }

  // TODO implement update action

}

