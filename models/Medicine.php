<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Medicine extends ActiveRecord
{

    public function getMedicineForm()
    {
        return $this->hasOne(MedicineForm::className(), ['medicine_form_id' => 'medicine_form_id']);
    }

    public static function tableName()
    {
        return '{{medicine}}';
    }
}
