<?php

namespace app\controllers;

use Yii;
use app\models\ParentData;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * ParentDataController implements the CRUD actions for ParentData model.
 */
class ParentDataController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ParentData models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ParentData::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ParentData model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
	
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    
 

 
    
    /**
     * Creates a new ParentData model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ParentData();

		$returnRoute = [];
		
		// Get foreign keys values if set through request parameters
		if(isset(Yii::$app->request->queryParams['parent_model_id'])){
			$model->parent_model_id = Yii::$app->request->queryParams['parent_model_id'];
		}
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
					
			//Check for session data
			$session = Yii::$app->session;
			$session->open();
			if($session->has('parent-data')){
				$returnRoute = [$session->get('parent-data')['route'],'id'=>$session->get('parent-data')['id']];
				$session->remove('parent-data');
			}
			$session->close();

			if(!empty($returnRoute)){
				return $this->redirect($returnRoute);
            }else{
				return $this->redirect(['view', 'id' => $model->id]);
			}
        } else {
			$noBreadcrumbs = false;
			if(Yii::$app->request->get('noBreadcrumbs') == true){
				$noBreadcrumbs = true;
			}
            return $this->render('create', [
                'model' => $model,
                'noBreadcrumbs' => $noBreadcrumbs,
            ]);
        }
    }

    /**
     * Updates an existing ParentData model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ParentData model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ParentData model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ParentData the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ParentData::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
