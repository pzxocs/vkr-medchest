<?php

namespace app\models;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class Recipe extends ActiveRecord
{
    public $medicine_name;
    public $user_name;

    public $issue_date_picker;
    public $valid_date_picker;

    public function rules()
    {
        return [
            [['recipe_id', 'author','medicine_id','medicine_name','comment', 'issue_date', 'valid_date', 'issue_date_picker', 'valid_date_picker','user_name', 'user_id'], 'safe'],
            ['issue_date', 'date', 'format' => 'php:Y-m-d'],
            ['issue_date_picker', 'date', 'format' => 'php:d.m.Y'],
            ['valid_date', 'date', 'format' => 'php:Y-m-d'],
            ['valid_date_picker', 'date', 'format' => 'php:d.m.Y'],
            ['issue_date_picker','validateIssue'],
            ['valid_date_picker','validateValid'],
            [['author'], 'required', 'message' => 'Введите имя доктора, выдавшего рецепт'],
            [['medicine_name'], 'required', 'message' => 'Заполните название препарата'],
            [['issue_date_picker'], 'required', 'message' => 'Заполните дату выдачи'],
            [['valid_date_picker'], 'required', 'message' => 'Заполните дату окончания срока действия'],
        ];
    }

    public function validateIssue(){
        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->issue_date_picker)->format("Y-m-d")) > strtotime(date('Y-m-d'))){
            $this->addError('issue_date_picker','Дата выдачи рецепта должна быть не позднее, чем сегодня');
        }
    }

    public function validateValid(){
        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->valid_date_picker)->format("Y-m-d")) <= strtotime(date('Y-m-d'))){
            $this->addError('valid_date_picker','Дата окончания рецепта должна быть позже, чем сегодня');
        }

        if(strtotime(DateTime::createFromFormat('d.m.Y', $this->valid_date_picker)->format("Y-m-d")) < strtotime(DateTime::createFromFormat('d.m.Y', $this->issue_date_picker)->format("Y-m-d")))
        {
            $this->addError('valid_date_picker','Дата окончания рецепта должна быть позже, чем дата выдачи');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedicine()
    {
        return $this->hasOne(Medicine::className(), ['medicine_id' => 'medicine_id']);
    }

    public static function tableName()
    {
        return '{{recipe}}';
    }

    public function edit()
    {
        //добавляем
        $dbrecipe = Recipe::find()->where(['recipe_id' => $this->recipe_id])
            ->one();

        $dbrecipe->author = $this->author;
        $dbrecipe->comment = $this->comment;
        $dbrecipe->issue_date = DateTime::createFromFormat('d.m.Y', $this->issue_date_picker)->format("Y-m-d");
        $dbrecipe->issue_date_picker = $this->issue_date_picker;
        $dbrecipe->valid_date= DateTime::createFromFormat('d.m.Y', $this->valid_date_picker)->format("Y-m-d");
        $dbrecipe->valid_date_picker = $this->valid_date_picker;

        $dbrecipe->medicine_name = $this->medicine_name;
        $dbrecipe->user_name = $this->user_name;

        //посмотрим, существует ли указанный препарат
        $dbmedicine = Medicine::find()
            ->where(['name' => $this->medicine_name])
            ->one();
        //если нет - то ничего не вставляем
        if($dbmedicine != null)
        {
            $dbrecipe->medicine_id = $dbmedicine->medicine_id;
            if($this->user_id == null)
            {
                $dbrecipe->user_id =  Yii::$app->user->id;
            }
            else
            {
                $dbrecipe->user_id =  intval($this->user_id);
            }

            $dbrecipe->save();
        }
    }

    public function createNew()
    {
        //добавляем
        $dbrecipe = new Recipe();
        $dbrecipe->author = $this->author;
        $dbrecipe->comment = $this->comment;
        $dbrecipe->issue_date = DateTime::createFromFormat('d.m.Y', $this->issue_date_picker)->format("Y-m-d");
        $dbrecipe->issue_date_picker = $this->issue_date_picker;
        $dbrecipe->valid_date= DateTime::createFromFormat('d.m.Y', $this->valid_date_picker)->format("Y-m-d");
        $dbrecipe->valid_date_picker = $this->valid_date_picker;

        $dbrecipe->medicine_name = $this->medicine_name;
        $dbrecipe->user_name = $this->user_name;

        //посмотрим, существует ли указанный препарат
        $dbmedicine = Medicine::find()
            ->where(['name' => $this->medicine_name])
            ->one();
        //если нет - то ничего не вставляем
        if($dbmedicine != null)
        {
            $dbrecipe->medicine_id = $dbmedicine->medicine_id;
            if($this->user_id == null)
            {
                $dbrecipe->user_id =  Yii::$app->user->id;
            }
            else
            {
                $dbrecipe->user_id =  intval($this->user_id);
            }

            $dbrecipe->save();
        }
    }
}

class RecipeSearch extends Recipe
{

    public function rules()
    {
        // только поля определенные в rules() будут доступны для поиска
        return [
            [['valid_date', 'issue_date'],'date'],
            [['recipe_id', 'author','medicine_name','comment', 'issue_date', 'valid_date', 'user_name'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Recipe::scenarios();
    }

    public function search($params)
    {
        $query = Recipe::find()
            ->where(['u1.id' => Yii::$app->user->id])
            ->orWhere(['u1.id' => \app\models\User::find()->where(['parent_id' => Yii::$app->user->id])->select(['id'])->asArray()])
            ->join('LEFT JOIN', 'user u1', 'u1.id = recipe.user_id')
            ->join('LEFT JOIN', 'user u2', 'u1.id = u2.parent_id')
            ->join('LEFT JOIN', 'medicine', 'medicine.medicine_id = recipe.medicine_id')
            ->select(['recipe_id as recipe_id', 'medicine.name as medicine_name', 'author as author', 'issue_date as issue_date',
                'valid_date as valid_date', 'is_used as is_used', 'comment as comment',
                'u1.name as user_name',
            ])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'user_name',
                    'medicine_name',
                    'author',
                    'issue_date',
                    'valid_date',
                    'is_used',
                    'comment',
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
            ->andFilterWhere(['like', 'issue_date', $this->issue_date])
            ->andFilterWhere(['like', 'valid_date', $this->valid_date])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'comment', $this->comment])
        ;

        return $dataProvider;
    }
}