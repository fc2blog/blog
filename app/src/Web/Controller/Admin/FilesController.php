<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\FilesModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class FilesController extends AdminController
{

    /**
     * 一覧表示 /admin/files/upload からPartial読み込み
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_index(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request)) {
            return $this->error403();
        }

        $files_model = new FilesModel();

        $blog_id = $this->getBlogId($request);

        $this->set('file_default_limit', Config::get('FILE.DEFAULT_LIMIT'));
        $this->set('page_list_file', App::getPageList($request, 'FILE'));
        $this->set('page_limit_file', App::getPageLimit($request, 'FILE'));
        // 検索条件
        $where = 'blog_id=?';
        $params = array($blog_id);

        if ($keyword = $request->get('keyword')) {
            $keyword = Model::escape_wildcard($keyword);
            $keyword = "%{$keyword}%";
            $where .= ' AND name LIKE ?';
            $params = array_merge($params, array($keyword));
        }

        // 並び順
        $order = 'created_at DESC, id DESC';
        switch ($request->get('order')) {
            default:
            case 'created_at_desc':
                break;
            case 'created_at_asc':
                $order = 'created_at ASC, id ASC';
                break;
            case 'name_desc':
                $order = 'name DESC, id DESC';
                break;
            case 'name_asc':
                $order = 'name ASC, id ASC';
                break;
        }

        $options = array(
            'where' => $where,
            'params' => $params,
            'limit' => $request->get('limit', App::getPageLimit($request, 'FILE'), Request::VALID_POSITIVE_INT),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => $order,
        );
        $files = $files_model->find('all', $options);
        $paging = $files_model->getPaging($options);

        foreach ($files as &$file) {
            $file['path'] = App::getUserFilePath($file, false, true);
        }

        $this->set('files', $files);
        $this->set('paging', $paging);
        $this->set('page_list_paging', Model::getPageList($paging));

        return 'admin/files/ajax_index.twig';
    }

    /**
     * 新規作成
     * @param Request $request
     * @return string
     */
    public function upload(Request $request): string
    {
        $files_model = new FilesModel();
        $blog_id = $this->getBlogId($request);

        $this->set('file_max_size', Config::get('FILE.MAX_SIZE'));
        $this->set('page_limit_file', App::getPageLimit($request, 'FILE'));

        // アップロード時処理
        if ($request->file('file')) {
            // 新規登録処理
            $errors = [];
            $errors['file'] = $files_model->insertValidate($request->file('file'), $request->get('file'), $data_file);
            if (empty($errors['file'])) {
                $data_file['blog_id'] = $blog_id;
                $tmp_name = $data_file['tmp_name'];
                unset($data_file['tmp_name']);
                if ($id = $files_model->insert($data_file)) {
                    // ファイルの移動
                    $data_file['id'] = $id;
                    $move_file_path = App::getUserFilePath($data_file, true);
                    App::mkdir($move_file_path);
                    if (defined("THIS_IS_TEST")) {
                        rename($tmp_name, $move_file_path);
                    } else {
                        move_uploaded_file($tmp_name, $move_file_path);
                    }

                    $this->setInfoMessage(__('I have completed the upload of files'));
                    $this->redirect($request, array('action' => 'upload')); // アップロード成功
                }
            }
            // 拡張子チェックエラーはファイルが指定されていない時には表示不要と思われるので、unset
            if (isset($errors['file']['file']) && isset($errors['file']['ext'])) {
                unset($errors['file']['ext']);
            }

            // エラー情報の設定
            $this->setErrorMessage(__('Input error exists'));
            $this->set('errors', $errors);
            return 'admin/files/upload.twig';
        }

        // PCの場合はajaxでファイル情報を取得するので以下の処理は不要
        if (App::isPC($request)) {
            return 'admin/files/upload.twig';
        }

        // 初期表示処理

        // 検索条件
        $where = 'blog_id=?';
        $params = array($blog_id);

        if ($keyword = $request->get('keyword')) {
            $keyword = Model::escape_wildcard($keyword);
            $keyword = "%{$keyword}%";
            $where .= ' AND name LIKE ?';
            $params = array_merge($params, array($keyword));
        }

        // 並び順
        $order = 'created_at DESC, id DESC';
        switch ($request->get('order')) {
            default:
            case 'created_at_desc':
                break;
            case 'created_at_asc':
                $order = 'created_at ASC, id ASC';
                break;
            case 'name_desc':
                $order = 'name DESC, id DESC';
                break;
            case 'name_asc':
                $order = 'name ASC, id ASC';
                break;
        }

        $options = array(
            'where' => $where,
            'params' => $params,
            'limit' => $request->get('limit', App::getPageLimit($request, 'FILE'), Request::VALID_POSITIVE_INT),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => $order,
        );
        $files = $files_model->find('all', $options);
        $paging = $files_model->getPaging($options);

        foreach ($files as &$file) {
            $file['path'] = App::getUserFilePath($file, false, true);
        }

        $this->set('files', $files);
        $this->set('paging', $paging);

        return 'admin/files/upload.twig';
    }

    /**
     *編集
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        $files_model = new FilesModel();
        $id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        $this->set('file_max_size', Config::get('FILE.MAX_SIZE'));

        // 詳細データの取得
        if (!$file = $files_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, ['action' => 'index']);
        }

        $file['path'] = App::getUserFilePath($file, false, true);
        $file['thumbnail_path'] = App::getThumbnailPath(App::getUserFilePath($file, false, true), 600, 'w');
        $this->set('file', $file);

        if (!$request->get('file')) {
            $request->set('file', $file);
            $back_url = $request->getReferer();
            if (!empty($back_url)) {
                $request->set('back_url', $back_url);    // 戻る用のURL
            }
            return 'admin/files/edit.twig';
        }

        if (!$request->isValidSig()) {
            $request = new Request();
            $this->redirect($request, ['action' => 'upload']);
        }

        // 新規登録処理
        $errors = [];
        $errors['file'] = $files_model->updateValidate($request->file('file'), $request->get('file'), $file, $data_file);
        if (empty($errors['file'])) {
            $tmp_name = $data_file['tmp_name'] ?? null;
            unset($data_file['tmp_name']);
            if ($files_model->updateByIdAndBlogId($data_file, $id, $blog_id)) {
                // ファイルの移動
                if (!empty($tmp_name)) {
                    $data_file['id'] = $id;
                    $data_file['blog_id'] = $blog_id;
                    $move_file_path = App::getUserFilePath($data_file, true);
                    App::deleteFile($blog_id, $id);
                    if (defined("THIS_IS_TEST")) {
                        rename($tmp_name, $move_file_path);
                    } else {
                        move_uploaded_file($tmp_name, $move_file_path);
                    }
                }

                $this->setInfoMessage(__('I have updated the file'));
                $back_url = $request->get('back_url');
                if (!empty($back_url)) {
                    $this->redirect($request, $back_url);
                }
                $this->redirect($request, ['action' => 'upload']);
            }
        }
        // 拡張子チェックエラーはファイルが指定されていない時には表示不要と思われるので、unset
        if (isset($errors['file']['file']) && isset($errors['file']['ext'])) {
            unset($errors['file']['ext']);
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        $back_url = $request->get('back_url');
        if (!empty($back_url)) {
            $request->set('back_url', $back_url);    // 戻る用のURL
        }
        return 'admin/files/edit.twig';
    }

    /**
     * 削除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
            // 削除処理
            if (Model::load('Files')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogId($request))) {
                $this->setInfoMessage(__('I removed the file'));
            } else {
                $this->setErrorMessage(__('I failed to remove'));
            }
        }

        // 元の画面へ戻る
        $back_url = $request->get('back_url');
        if (!empty($back_url)) {
            $this->redirect($request, $back_url);
        }
        $this->redirect($request, array('action' => 'upload'));
    }

    /**
     * 削除
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_delete(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request) || $request->method !== 'POST' || !$request->isValidSig()) {
            return $this->error403();
        }

        // 削除処理
        $json = array('status' => 0);
        if (!Model::load('Files')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogId($request))) {
            $json = array('status' => 1);
        }

        $this->setContentType("application/json; charset=utf-8");
        $this->set('json', $json);
        return "admin/common/json.twig";
    }

    /**
     * ajaxによるファイルアップロード受付
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_file_upload(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request) || $request->method !== 'POST' || !$request->isValidSig()) {
            return $this->error403();
        }

        $files_model = new FilesModel();
        $blog_id = $this->getBlogId($request);

        // アップロード時処理
        if ($request->file('file')) {
            // 新規登録処理
            $errors = [];
            $errors['file'] = $files_model->insertValidate($request->file('file'), $request->get('file'), $data_file);
            if (empty($errors['file'])) {
                $data_file['blog_id'] = $blog_id;
                $tmp_name = $data_file['tmp_name'];
                unset($data_file['tmp_name']);
                if ($id = $files_model->insert($data_file)) {
                    // ファイルの移動
                    $data_file['id'] = $id;
                    $move_file_path = App::getUserFilePath($data_file, true);
                    App::mkdir($move_file_path);
                    if (defined("THIS_IS_TEST")) {
                        rename($tmp_name, $move_file_path);
                    } else {
                        move_uploaded_file($tmp_name, $move_file_path);
                    }
                    $this->setContentType("application/json; charset=utf-8");
                    $this->set('json', ['status' => 'ok']);
                    return "admin/common/json.twig";
                }
            }
        }
        // 拡張子チェックエラーはファイルが指定されていない時には表示不要と思われるので、unset
        if (isset($errors['file']['file']) && isset($errors['file']['ext'])) {
            unset($errors['file']['ext']);
        }

        $this->setContentType("application/json; charset=utf-8");
        $this->set('json', ['status' => 'ng']);
        return "admin/common/json.twig";
    }
}

