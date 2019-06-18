<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MedicineForm extends ActiveRecord
{
    public static function tableName()
    {
        return '{{medicine_form}}';
    }
}
