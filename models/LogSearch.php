<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Log;

/**
 * LogSearch represents the model behind the search form of `app\models\Log`.
 */
class LogSearch extends Log
{
    public $dateStart;
    public $dateEnd;
//?LogSearch%5Bid%5D=&LogSearch%5Bip%5D=&LogSearch%5Bdate%5D=&LogSearch%5Burl%5D=&LogSearch%5Bos%5D=&dateStart=22.03.2019&dateEnd=23.03.2019

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['ip', 'url', 'os', 'x_bit', 'browser', 'date', 'dateStart'], 'safe'],
            ['dateEnd', function ($attribute, $params) {
                $start = $this->convertToUnixTime($this->dateStart);
                $end = $this->convertToUnixTime($this->dateEnd);
                if ( ($end - $start) > 31536000) {
                    $this->addError($attribute, 'Выбрынный период больше года.');
                }
            }]
        ];
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
    public function search($params)
    {

        $query = Log::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
           // 'date' => $this->date,
        ]);

        // поиск по дням, формат: 21.03.2019
        if ($this->date != null) {
            $timestamp = $this->convertToUnixTime($this->date);
            $query->andFilterWhere(['AND',['>', 'date', $timestamp],['<', 'date', $timestamp+60*60*24]]);
        }

        if ($this->dateStart != null) {
            $timestamp = $this->convertToUnixTime($this->dateStart);
            $query->andFilterWhere(['>', 'date', $timestamp]);
        }

        if ($this->dateEnd != null) {
            $timestamp = $this->convertToUnixTime($this->dateEnd);
            $query->andFilterWhere(['<', 'date', $timestamp]);
        }

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'x_bit', $this->x_bit])
            ->andFilterWhere(['like', 'browser', $this->browser]);

        return $dataProvider;
    }


}
