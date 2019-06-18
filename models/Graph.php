<?php

namespace app\models;

use DateTime;
use Yii;
use yii\db\ActiveRecord;

class Graph extends ActiveRecord
{
    public $plan_date;
    public $hours;
    public $minutes;
    public $message;

    public function rules()
    {
        return [
            [['course_id', 'graph_id','plan_take_date', 'doze','hours','minutes','is_before_eat','is_after_eat'], 'safe'],
            //['plan_take_date', 'date', 'format' => 'Y-m-d H:i:s'],
            [['is_before_eat','is_after_eat'], 'boolean'],
            [['minutes'], 'required', 'message' => 'Заполните поле'],
            [['hours'], 'required', 'message' => 'Заполните поле'],
            [['doze'], 'double'],
        ];
    }

    public static function tableName()
    {
        return '{{graph}}';
    }

    public function isNeedAlert()
    {
        $course = Course::find()->where(['course_id' => $this->course_id])->one();
        $dbuserprefs = UserPreferences::find()->where(['user_id' => $course->user_id])->one();
        if($dbuserprefs == null)
        {
            return 0;
        }
        $alertLevel = $dbuserprefs->alert_critical_pcs_left;

        if($alertLevel == null)
        {
            return 0;
        }

        //получим общее количество медикамента
        $med_id =  $course->medicine_id;
        $meditems = MedicineItem::find()->where(['medicine_id' => $med_id,'user_id' => $course->user_id])->all();
        $pcsCount = Medicine::find()->where(['medicine_id' => $med_id])->one()->pcs;

        foreach ($meditems as &$meditem) {
            if($meditem->pcs_left < $pcsCount * ($alertLevel/100))
            {
                return $meditem->medicine_item_id;
            }
        }
    }

    public function reduceOne()
    {
        $course = Course::find()->where(['course_id' => $this->course_id])->one();

        //получим общее количество медикамента
        $med_id =  $course->medicine_id;
        //берем где больше всех
        $meditem = MedicineItem::find()->where(['medicine_id' => $med_id,'user_id' => $course->user_id])->orderBy(['pcs_left'=>SORT_DESC])->one();
        if($meditem != null && $meditem->pcs_left > 0)
        {
            $meditem->pcs_left = intval($meditem->pcs_left) - intval($this->doze);

            $meditem->expired_picker = '01.01.2049';
            //заолнимм недостающие поля
            $meditem->medicine_name = "aaa";
            $meditem->medicine_form = "aaa";
            $meditem->active_material = "aaa";
            $meditem->storage_place = "aaa";
            $meditem->pcs = 0;
            $meditem->dosage = 0;


            $meditem->save();
            return true;
        }
        return false;
    }

    public function editGraph()
    {
        $newGraph = Graph::find()->where(['graph_id' => $this->graph_id])->one();
        $newGraph->is_before_eat = $this->is_before_eat;
        $newGraph->is_after_eat = $this->is_after_eat;
        //редактируем время приема
        $dt = new DateTime($newGraph->plan_take_date);
        $dt->setTime($this->hours, $this->minutes);
        $newGraph->minutes = $this->minutes;
        $newGraph->hours = $this->minutes;
        $newGraph->plan_take_date = $dt->format("Y-m-d H:i:s");
        $newGraph->save();
    }
}
