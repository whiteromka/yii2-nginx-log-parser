<?php

namespace app\controllers;

use Yii;
use app\models\Log;
use app\models\LogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LogController implements the CRUD actions for Log model.
 */
class LogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Log models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $where = $dataProvider->query->where ?? [];

        $popularBrowsers = Log::find()->asArray()
            ->select(['browser as label, COUNT(id) as value'])
            ->andFilterWhere($where)
            ->groupBy('label')
            ->orderBy(['value'=>SORT_DESC])
            ->limit(3)
            ->all();

        $queries = (new Log())->getQueries($where);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'popularBrowsers' => $popularBrowsers,
            'queries' => $queries
        ]);
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
