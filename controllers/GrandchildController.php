<?php

namespace app\controllers;

use Yii;
use app\models\Grandchild;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * GrandchildController implements the CRUD actions for Grandchild model.
 */
class GrandchildController extends Controller
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
     * Lists all Grandchild models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Grandchild::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grandchild model.
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
     * Creates a new Grandchild model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Grandchild();

		$returnRoute = [];
		
		// Get foreign keys values if set through request parameters
		if(isset(Yii::$app->request->queryParams['child_id'])){
			$model->child_id = Yii::$app->request->queryParams['child_id'];
		}
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
					
			//Check for session data
			$session = Yii::$app->session;
			$session->open();
			if($session->has('grandchild')){
				$returnRoute = [$session->get('grandchild')['route'],'id'=>$session->get('grandchild')['id']];
				$session->remove('grandchild');
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
     * Updates an existing Grandchild model.
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
     * Deletes an existing Grandchild model.
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
     * Finds the Grandchild model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Grandchild the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grandchild::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
