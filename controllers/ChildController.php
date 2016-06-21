<?php

namespace app\controllers;

use Yii;
use app\models\Child;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Grandchild;


/**
 * ChildController implements the CRUD actions for Child model.
 */
class ChildController extends Controller
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
     * Lists all Child models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Child::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Child model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$grandchildProvider = new ActiveDataProvider([
			'query'=>Grandchild::find()->where(['child_id'=>$id])
		]);

		if(in_array('Grandchild',array_keys(Yii::$app->request->post()))){
			$child = new Grandchild;
			$child->attributes = Yii::$app->request->post('Grandchild');
			if(empty($child->name)){
				$child->name = 'Grandchild #'.strval(Grandchild::find()->where(['child_id'=>$id])->count()+1);
			}
			if(!($child->save())){
				throw new \yii\web\HttpException(404, 'Could not save child model');
			}
			Yii::$app->session->setFlash('grandchild-flash','Grandchild Created Successfully!');
		}

	
        return $this->render('view', [
            'model' => $this->findModel($id),
			'grandchildProvider' => $grandchildProvider,
        ]);
    }
    
    
 

    
    /**
     * Creates a new Child model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Child();

		$returnRoute = [];
		
		// Get foreign keys values if set through request parameters
		if(isset(Yii::$app->request->queryParams['parent_id'])){
			$model->parent_id = Yii::$app->request->queryParams['parent_id'];
		}
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
					
			//Check for session data
			$session = Yii::$app->session;
			$session->open();
			if($session->has('child')){
				$returnRoute = [$session->get('child')['route'],'id'=>$session->get('child')['id']];
				$session->remove('child');
			}
			$session->close();

			Yii::$app->session->setFlash('child-flash','Child Created Successfully!');
			
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
     * Updates an existing Child model.
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
     * Deletes an existing Child model.
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
     * Finds the Child model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Child the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Child::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
