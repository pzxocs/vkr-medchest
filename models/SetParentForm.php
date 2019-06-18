<?php

namespace app\models;

use Yii;
use yii\base\Model;


class SetParentForm extends Model
{
    public $parent_id;
    public $user_login;
    public $user_name;

    public function rules()
    {
        return [
            [['parent_id', 'user_login','user_name'], 'safe'],
        ];
    }

    function __construct() {
        parent::__construct();
        $dbuser = User::find()
            ->where(['id' => Yii::$app->user->id])
            ->one();
        if($dbuser != null)
        {
            $this->parent_id = $dbuser->parent_id;
            // заполним
            $dbparent = User::find()
                ->where(['id' => $this->parent_id])
                ->one();

            if($dbparent != null)
            {
                $this->user_login = $dbparent->username;
                $this->user_name = $dbparent->name;
            }
        }
    }
}

