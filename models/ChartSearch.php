<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ChartSearch represents the model behind the search form of `app\models\Upload`.
 */
class ChartSearch extends Upload
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'user_id'], 'integer'],
            [['name', 'filename', 'size', 'date'], 'safe'],
            ['date', 'validateDate'],
        ];
    }

    public function validateDate($attribute, $param)
    {
        if (!empty($this->$attribute)) { // Проверка на заполнение. Если не заполняли, считаем, что всё ок

            $date = explode(' - ', $this->$attribute); // разбиваем содержимое атрибута

            if (!isset($date[0]) || !isset($date[1])) { //проверяем, что интервал передан корректно
                $this->addError($attribute, 'Неверный формат интервала дат'); // Если некорректно - добавляем ошибку
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Upload::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function searchCounter($params)
    {
        $this->load($params);


        if (!$this->validate()) {
            return new UploadCounter();
        }

        $query = Upload::find()
            ->select([
                'countPublic' => 'COUNT(IF(`type` = 0, 1, NULL))',
                'countProtected' => 'COUNT(IF(`type` = 1, 1, NULL))',
                'countPrivate' => 'COUNT(IF(`type` = 2, 1, NULL))'
            ])
            ->asArray();

        //+ ограничения по интервалу выборки, н-р
        if ($this->date != '') {
            $date = explode(' - ', $this->date);

            if ($this->date != '') {
                $date = explode(' - ', $this->date);

                $query->andWhere(['>=', 'date', date('Y-m-d', strtotime($date[0]))]);
                $query->andWhere(['<=', 'date', date('Y-m-d', strtotime($date[1]))]);
            }
        }
        $data = $query->one();
        $model = new UploadCounter();
        $model->load($data, '');

        return $model;
    }


}
