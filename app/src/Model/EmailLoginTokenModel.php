<?php
declare(strict_types=1);

namespace Fc2blog\Model;

class EmailLoginTokenModel extends Model
{
    public function getTableName(): string
    {
        return 'email_login_token';
    }

    /**
     * バリデート処理
     * @param array $data
     * @param array|null $valid_data
     * @param array $white_list
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        // バリデートを定義
        $this->validates = array(
            'login_id' => array(
                'required' => true,
                'maxlength' => array('max' => 512),
            ),
        );

        return parent::validate($data, $valid_data, $white_list);
    }

    /**
     * @param array $values
     * @param array $options
     * @return int|null last insert id or null
     */
    public function insert(array $values, array $options = []): ?int
    {
        unset($values['id']); // insertのため、pkを削除
        $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
        $last_insert_id = parent::insert($values, $options);
        return $last_insert_id === false ? null : (int)$last_insert_id;
    }
}
