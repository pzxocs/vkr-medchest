<?php

namespace app\models;

use DateInterval;
use DatePeriod;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class Course extends ActiveRecord
{
    public $medicine_name;
    public $dosage;
    public $takes;

    public $is_before_eat;
    public $is_after_eat;

    public $graph_list;

    public $begin_date_picker;
    public $end_date_picker;

    public function rules()
    {
        return [
            [['course_id', 'medicine_item_id','medicine_name', 'begin_date', 'end_date', 'begin_date_picker', 'end_date_picker','dosage','takes','is_before_eat', 'is_after_eat','user_id'], 'safe'],
            ['begin_date', 'date', 'format' => 'php:Y-m-d'],
            ['end_date', 'date', 'format' => 'php:Y-m-d'],
            ['begin_date_picker', 'date', 'format' => 'php:d.m.Y'],
            ['end_date_picker', 'date', 'format' => 'php:d.m.Y'],
            ['begin_date_picker','validateBegin'],
            ['end_date_picker','validateEnd'],
            [['medicine_name'], 'required', 'message' => 'Заполните название препарата'],
            [['begin_date_picker'], 'required', 'message' => 'Заполните дату начала курса'],
            [['end_date_picker'], 'required', 'message' => 'Заполните дату окончания курса'],
            [['dosage'], 'required', 'message' => 'Введите дозировку на прием'],
            [['takes'], 'required', 'message' => 'Введите количестов приемов в день'],
            [['dosage', 'takes'], 'double'],
        ];
    }

    public function validateBegin(){
        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->begin_date_picker)->format("Y-m-d")) < strtotime(date('Y-m-d'))){
            $this->addError('begin_date_picker','Дата начала курса - как минимум сегодня');
        }
    }

    public function validateEnd(){
        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->end_date_picker)->format("Y-m-d")) < strtotime(date('Y-m-d'))){
            $this->addError('end_date_picker','Дата окончания курса - как минимум сегодня');
        }

        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->end_date_picker)->format("Y-m-d")) < strtotime(DateTime::createFromFormat('d.m.Y', $this->begin_date_picker)->format("Y-m-d")))
        {
            $this->addError('end_date_picker','Дата окончания курса не может быть раньше даты начала');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGraph()
    {
        return $this->hasMany(Graph::className(), ['course_id' => 'course_id']);
    }

    public function getMedicine()
    {
        return $this->hasOne(Medicine::className(), ['medicine_id' => 'medicine_id']);
    }

    public static function tableName()
    {
        return '{{course}}';
    }

    public function createNew()
    {
        //добавляем
        $dbcourse = new Course();
        if($this->user_id == null)
        {
            $dbcourse->user_id =  Yii::$app->user->id;
        }
        else
        {
            $dbcourse->user_id =  intval($this->user_id);
        }

        $dbcourse->begin_date = DateTime::createFromFormat('d.m.Y', $this->begin_date_picker)->format("Y-m-d");
        $dbcourse->end_date= DateTime::createFromFormat('d.m.Y', $this->end_date_picker)->format("Y-m-d");

        $dbcourse->medicine_name = $this->medicine_name;
        $dbcourse->dosage = $this->dosage;
        $dbcourse->takes = $this->takes;

        $dbcourse->begin_date_picker = $this->begin_date_picker;
        $dbcourse->end_date_picker = $this->end_date_picker;

        //посмотрим, существует ли указанный препарат
        $dbmedicine = Medicine::find()
            ->where(['name' => $this->medicine_name])
            ->one();
        //если нет - то ничего не вставляем
        if($dbmedicine != null)
        {
            $dbcourse->medicine_id = $dbmedicine->medicine_id;
            $dbcourse->save();
            //создаем точки приема в зависимости от указанных дозировок
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod(new DateTime($dbcourse->begin_date), $interval, new DateTime($dbcourse->end_date));

            foreach ($period as $dt) {
                //посчитаем по сколько часов прибавлять
                $hour = intval(16 / $this->takes);
                if($this->takes > 1)
                {
                    for ($i = 0; $i < $this->takes; $i++)
                    {
                        $dbgraph = new Graph();
                        $dbgraph-> course_id = $dbcourse->course_id;
                        $date = new DateTime($dt->format('Y-m-d'));
                        $date->modify('+'.(8 + ($hour * $i)).' hour');
                        $dbgraph->plan_take_date = $date->format("Y-m-d H:i:s");
                        $dbgraph->doze = $this->dosage;
                        $dbgraph->is_before_eat = $this->is_before_eat;
                        $dbgraph->is_after_eat = $this->is_after_eat;
                        $dbgraph->hours = 0;
                        $dbgraph->minutes = 0;
                        $dbgraph->save();
                    }
                }
                else
                {
                    $dbgraph = new Graph();
                    $dbgraph-> course_id = $dbcourse->course_id;
                    $date = new DateTime($dt->format('Y-m-d'));
                    $date->modify('+8 hour');
                    $dbgraph->plan_take_date = $date->format("Y-m-d H:i:s");
                    $dbgraph->doze = $this->dosage;
                    $dbgraph->is_before_eat = $this->is_before_eat;
                    $dbgraph->is_after_eat = $this->is_after_eat;
                    $dbgraph->hours = 0;
                    $dbgraph->minutes = 0;
                    $dbgraph->save();
                }
            }

        }
    }

}