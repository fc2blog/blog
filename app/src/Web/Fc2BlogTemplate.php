<?php
declare(strict_types=1);

namespace Fc2blog\Web;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Web\Controller\User\UserController;

class Fc2BlogTemplate
{
    /**
     * fc2blog形式のPHP Viewテンプレート内で利用する各種データを生成・変換
     * @param Request $request
     * @param array $data
     * @return array
     */
    static public function preprocessingData(Request $request, array $data): array
    {
        $data['request'] = $request;

        // FC2のテンプレート用にデータを置き換える
        if (!empty($data['entry'])) {
            $data['entries'] = [$data['entry']];
        }
        if (!empty($data['entries'])) {
            foreach ($data['entries'] as $key => $value) {
                // topentry系変数のデータ設定
                $data['entries'][$key]['title_w_img'] = $value['title'];
                $data['entries'][$key]['title'] = strip_tags($value['title']);
                $data['entries'][$key]['link'] = App::userURL($request, ['controller' => 'Entries', 'action' => 'view', 'blog_id' => $value['blog_id'], 'id' => $value['id']]);

                [
                    $data['entries'][$key]['year'],
                    $data['entries'][$key]['month'],
                    $data['entries'][$key]['day'],
                    $data['entries'][$key]['hour'],
                    $data['entries'][$key]['minute'],
                    $data['entries'][$key]['second'],
                    $data['entries'][$key]['youbi'],
                    $data['entries'][$key]['month_short']
                ] = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($value['posted_at'])));
                $data['entries'][$key]['wayoubi'] = __($data['entries'][$key]['youbi']);

                // 自動改行処理
                if ($value['auto_linefeed'] == Config::get('ENTRY.AUTO_LINEFEED.USE')) {
                    $data['entries'][$key]['body'] = nl2br($value['body']);
                    $data['entries'][$key]['extend'] = nl2br($value['extend']);
                }

                // topentry_enc_* 系タグの生成
                $data['entries'][$key]['enc_title'] = urlencode($data['entries'][$key]['title']);
                $data['entries'][$key]['enc_utftitle'] = urlencode($data['entries'][$key]['title']);
                $data['entries'][$key]['enc_link'] = urlencode($data['entries'][$key]['link']);
            }
        }

        // コメント一覧の情報
        if (!empty($data['comments'])) {
            foreach ($data['comments'] as $key => $value) {
                $data['comments'][$key]['edit_link'] = Html::url($request, ['controller' => 'Entries', 'action' => 'comment_edit', 'blog_id' => $value['blog_id'], 'id' => $value['id']]);

                [
                    $data['comments'][$key]['year'],
                    $data['comments'][$key]['month'],
                    $data['comments'][$key]['day'],
                    $data['comments'][$key]['hour'],
                    $data['comments'][$key]['minute'],
                    $data['comments'][$key]['second'],
                    $data['comments'][$key]['youbi']
                ] = explode('/', date('Y/m/d/H/i/s/D', strtotime($value['updated_at'])));
                $data['comments'][$key]['wayoubi'] = __($data['comments'][$key]['youbi']);
                $data['comments'][$key]['body'] = $value['body']; // TODO nl2brされていないのは正しいのか？

                $value['reply_updated_at'] = $value['reply_updated_at'] ?? ""; // reply_updated_at is nullable
                $reply_updated_at = strtotime($value['reply_updated_at']) ?: 0;
                [
                    $data['comments'][$key]['reply_year'],
                    $data['comments'][$key]['reply_month'],
                    $data['comments'][$key]['reply_day'],
                    $data['comments'][$key]['reply_hour'],
                    $data['comments'][$key]['reply_minute'],
                    $data['comments'][$key]['reply_second'],
                    $data['comments'][$key]['reply_youbi']
                ] = explode('/', date('Y/m/d/H/i/s/D', $reply_updated_at));
                $data['comments'][$key]['reply_wayoubi'] = __($data['comments'][$key]['reply_youbi']);
                $data['comments'][$key]['reply_body'] = nl2br((string)$value['reply_body']);
            }
        }

        // FC2用のどこでも有効な単変数
        $data['blog_id'] = UserController::getBlogId($request); // TODO User系でしかこのメソッドは呼ばれないはずなので
        if ($data['blog_id'] !== Config::get('DEFAULT_BLOG_ID')) {
            $data['url'] = '/' . $data['blog']['id'] . '/';
        } else {
            // シングルテナントモード、DEFAULT_BLOG_IDとBlogIdが一致するなら、Pathを省略する
            $data['url'] = '/';
        }

        // 年月日系
        $data['now_date'] = (isset($data['date_area']) && $data['date_area']) ? $data['now_date'] : date('Y-m-d');
        $data['now_month_date'] = date('Y-m-1', strtotime($data['now_date']));
        $data['prev_month_date'] = date('Y-m-1', strtotime($data['now_month_date'] . ' -1 month'));
        $data['next_month_date'] = date('Y-m-1', strtotime($data['now_month_date'] . ' +1 month'));

        return $data;
    }

}