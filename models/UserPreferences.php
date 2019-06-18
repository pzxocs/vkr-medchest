<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserPreferences extends ActiveRecord
{
    public static function tableName()
    {
        return '{{user_preferences}}';
    }
}
