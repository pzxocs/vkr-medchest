<?php

namespace app\models;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class ChildList extends User
{
    public function rules()
    {
        // только поля определенные в rules() будут доступны для поиска
        return [
            [['username','name','email','phone'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return User::scenarios();
    }

    public function search($params)
    {
        $query = User::find()->where(['parent_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            ->andFilterWhere(['like', 'expired', $this->username])
            ->andFilterWhere(['like', 'expired', $this->name])
            ->andFilterWhere(['like', 'dosage', $this->email])
            ->andFilterWhere(['like', 'pcs_left', $this->phone])
        ;

        return $dataProvider;
    }
}
