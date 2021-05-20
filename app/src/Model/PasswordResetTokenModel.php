<?php
declare(strict_types=1);

namespace Fc2blog\Model;

class PasswordResetTokenModel extends Model
{
    public function getTableName(): string
    {
        return 'password_reset_token';
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
     * @return int last insert id
     */
    public function insert(array $values, array $options = []): int
    {
        unset($values['id']); // insertのため、pkを削除
        $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
        return (int)parent::insert($values, $options);
    }
}
