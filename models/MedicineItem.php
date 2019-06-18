<?php

namespace app\models;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class MedicineItem extends ActiveRecord
{
    public $medicine_name;
    public $medicine_form;
    public $active_material;
    public $storage_place;
    public $user_name;
    public $pcs;

    public $expired_picker;

    public function rules()
    {
        return [
            [['medicine_item_id', 'medicine_form', 'medicine_name','storage_place','active_material', 'expired', 'expired_picker', 'dosage', 'pcs_left','user_name', 'user_id', 'pcs'], 'safe'],
            ['expired', 'date', 'format' =>'php:Y-m-d'],
            ['expired_picker', 'date', 'format' =>'php:d.m.Y'],
            ['expired_picker','validateExpired'],
            [['medicine_form'], 'required', 'message' => 'Заполните лекарственную форму'],
            [['medicine_name'], 'required', 'message' => 'Заполните название'],
            [['storage_place'], 'required', 'message' => 'Заполните условия хранения'],
            [['active_material'], 'required', 'message' => 'Заполните действующее вещество'],
            [['expired_picker'], 'required', 'message' => 'Заполните дату окончания срока годности'],
            [['dosage'], 'required', 'message' => 'Заполните дозировку'],
            [['pcs_left'], 'required', 'message' => 'Заполните остаток'],
            [['pcs'], 'required', 'message' => 'Заполните количество'],
        ];
    }

    public function validateExpired(){
        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->expired_picker)->format("Y-m-d")) <= strtotime(date('Y-m-d'))){
            $this->addError('expired_picker','Невозможно добавить просроченное лекарство');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedicine()
    {
        return $this->hasOne(Medicine::className(), ['medicine_id' => 'medicine_id'])->with(['medicineForm']);
    }

    public static function tableName()
    {
        return '{{medicine_item}}';
    }

    public function isExpiring($date)
    {
        //проверяем, близко ли срок годности
        $dbuserprefs = UserPreferences::find()->where(['user_id' => $this->user_id])->one();
        if($dbuserprefs == null)
        {
            return false;
        }
        $alertLevel = $dbuserprefs->alert_days_to_expired;

        if($alertLevel == null)
        {
            return false;
        }

        if (strtotime($date) > strtotime($this->expired))
        {
            return true;
        }
        //вычисляем разницу между датой истечения и текущей
        $interval = date_diff(DateTime::createFromFormat('Y-m-d', $date), DateTime::createFromFormat('Y-m-d', $this->expired));

        return intval($interval->days) < intval($alertLevel);
    }

    public function createNew()
    {
        //проверяем, есть ли такое лекарстов
        $dbmedicine = Medicine::find()
            ->where(['name' => $this->medicine_name])
            ->one();


        if($dbmedicine == null)
        {
            //если нет, сперва добавляем
            $dbmedicine = new Medicine();
            $dbmedicine->name = $this->medicine_name;
            $dbmedicine->storage_place = $this->storage_place;
            $dbmedicine->pcs = $this->pcs;
            $dbmedicine->dosage= $this->dosage;
            $dbmedicine->active_material = $this->active_material;

            //посмотрим, существует ли указанная форма
            $dbmedicineForm = MedicineForm::find()
                ->where(['name' => $this->medicine_form])
                ->one();
            if($dbmedicineForm == null)
            {
                //создадим новую форму
                $dbmedicineForm = new MedicineForm();
                $dbmedicineForm->name = $this->medicine_form;
                $dbmedicineForm->unit_meas = 'шт';
                $dbmedicineForm->save();
            }
            $dbmedicine->medicine_form_id = $dbmedicineForm->medicine_form_id;
            $dbmedicine->save();
        }
        //добавляем собственно итем
        $dbmeditem = new MedicineItem();
        $dbmeditem->medicine_id = $dbmedicine->medicine_id;
        $dbmeditem->expired = DateTime::createFromFormat('d.m.Y', $this->expired_picker)->format("Y-m-d");
        $dbmeditem->expired_picker = $this->expired_picker;
        //заолнимм недостающие поля
        $dbmeditem->medicine_name = $this->medicine_name;
        $dbmeditem->medicine_form = $this->medicine_form;
        $dbmeditem->active_material = $this->active_material;
        $dbmeditem->storage_place = $this->storage_place;
        $dbmeditem->pcs = $this->pcs;
        $dbmeditem->dosage = $this->dosage;
        $dbmeditem->pcs_left = $this->pcs_left;
        if($this->user_id == null)
        {
            $dbmeditem->user_id =  Yii::$app->user->id;
        }
        else
        {
            $dbmeditem->user_id =  intval($this->user_id);
        }

        $dbmeditem->save();
    }

    public function edit()
    {
        //проверяем, есть ли такое лекарстов
        $dbmedicine = Medicine::find()
            ->where(['name' => $this->medicine_name])
            ->one();


        if($dbmedicine == null)
        {
            //если нет, сперва добавляем
            $dbmedicine = new Medicine();
            $dbmedicine->name = $this->medicine_name;
            $dbmedicine->storage_place = $this->storage_place;
            $dbmedicine->pcs = $this->pcs;
            $dbmedicine->dosage= $this->dosage;
            $dbmedicine->active_material = $this->active_material;

            //посмотрим, существует ли указанная форма
            $dbmedicineForm = MedicineForm::find()
                ->where(['name' => $this->medicine_form])
                ->one();
            if($dbmedicineForm == null)
            {
                //создадим новую форму
                $dbmedicineForm = new MedicineForm();
                $dbmedicineForm->name = $this->medicine_form;
                $dbmedicineForm->unit_meas = 'шт';
                $dbmedicineForm->save();
            }
            $dbmedicine->medicine_form_id = $dbmedicineForm->medicine_form_id;
            $dbmedicine->save();
        }
        //добавляем собственно итем
        $dbmeditem = MedicineItem::find()->where(['medicine_item_id' => $this->medicine_item_id])->one();
        $dbmeditem->medicine_id = $dbmedicine->medicine_id;
        $dbmeditem->expired = DateTime::createFromFormat('d.m.Y', $this->expired_picker)->format("Y-m-d");
        $dbmeditem->expired_picker = $this->expired_picker;
        //заолнимм недостающие поля
        $dbmeditem->medicine_name = $this->medicine_name;
        $dbmeditem->medicine_form = $this->medicine_form;
        $dbmeditem->active_material = $this->active_material;
        $dbmeditem->storage_place = $this->storage_place;
        $dbmeditem->pcs = $this->pcs;
        $dbmeditem->dosage = $this->dosage;
        $dbmeditem->pcs_left = $this->pcs_left;
        if($this->user_id == null)
        {
            $dbmeditem->user_id =  Yii::$app->user->id;
        }
        else
        {
            $dbmeditem->user_id =  intval($this->user_id);
        }

        $dbmeditem->save();
    }
}

class MedItemSearch extends MedicineItem
{
    public function rules()
    {
        // только поля определенные в rules() будут доступны для поиска
        return [
            [['dosage', 'pcs_left'], 'double'],
            [['expired'],'date'],
            [['medicine_name','medicine_form','active_material','storage_place' ,'expired', 'dosage','pcs_left','user_name','pcs'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return MedicineItem::scenarios();
    }

    public function search($params)
    {
        $query = MedicineItem::find()
            ->where(['u1.id' => Yii::$app->user->id])
            ->orWhere(['u1.id' => \app\models\User::find()->where(['parent_id' => Yii::$app->user->id])->select(['id'])->asArray()])
            ->join('LEFT JOIN', 'user u1', 'u1.id = medicine_item.user_id')
            ->join('LEFT JOIN', 'user u2', 'u1.id = u2.parent_id')
            ->join('LEFT JOIN', 'medicine', 'medicine.medicine_id = medicine_item.medicine_id')
            ->join('LEFT JOIN', 'medicine_form', 'medicine.medicine_form_id = medicine_form.medicine_form_id')
            ->select(['medicine_item_id as medicine_item_id', 'medicine.name as medicine_name', 'medicine_form.name as medicine_form', 'medicine.active_material as active_material',
                'medicine.storage_place as storage_place', 'expired as expired','medicine_item.dosage as dosage', 'pcs_left as pcs_left','medicine.pcs as pcs',
                'u1.name as user_name',
            ])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'user_name',
                    'medicine_name',
                    'medicine_form',
                    'active_material',
                    'storage_place',
                    'expired',
                    'dosage',
                    'pcs_left',
                    'pcs'
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);


        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

         // изменяем запрос добавляя в его фильтрацию
        $query
            ->andFilterWhere(['like', 'u1.name', $this->user_name])
            ->andFilterWhere(['like', 'medicine.name', $this->medicine_name])
            ->andFilterWhere(['like', 'medicine_form.name', $this->medicine_form])
            ->andFilterWhere(['like', 'medicine.active_material', $this->active_material])
            ->andFilterWhere(['like', 'medicine.storage_place', $this->storage_place])
            ->andFilterWhere(['like', 'expired', $this->expired])
            ->andFilterWhere(['like', 'dosage', $this->dosage])
            ->andFilterWhere(['like', 'pcs_left', $this->pcs_left])
            ->andFilterWhere(['like', 'medicine.pcs', $this->pcs])
        ;

        return $dataProvider;
    }
}
