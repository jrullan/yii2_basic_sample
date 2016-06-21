<?php

namespace app\controllers;

use Yii;
use app\models\ParentModel;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Child;
use app\models\ParentData;


/**
 * ParentModelController implements the CRUD actions for ParentModel model.
 */
class ParentModelController extends Controller
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
     * Lists all ParentModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ParentModel::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ParentModel model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$childProvider = new ActiveDataProvider([
			'query'=>Child::find()->where(['parent_id'=>$id])
		]);

		if(in_array('Child',array_keys(Yii::$app->request->post()))){
			$child = new Child;
			$child->attributes = Yii::$app->request->post('Child');
			if(empty($child->name)){
				$child->name = 'Child #'.strval(Child::find()->where(['parent_id'=>$id])->count()+1);
			}
			if(!($child->save())){
				throw new \yii\web\HttpException(404, 'Could not save child model');
			}
		}

		$parentDataModel = new ParentData();

	
        return $this->render('view', [
            'model' => $this->findModel($id),
			'childProvider' => $childProvider,
			'parentDataModel' => $parentDataModel,
        ]);
    }
    
    
 


	public function actionAddParentData($id)
	{
		$session = Yii::$app->session;
		$session->open();
		$session->set('parent-data',['route'=>'parent-model/view','id'=>$id]);
		$session->close();
		return $this->redirect(['parent-data/create','parent_model_id'=>$id]);
	}

 
    
    /**
     * Creates a new ParentModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ParentModel();

		$returnRoute = [];
		
				
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
					
			//Check for session data
			$session = Yii::$app->session;
			$session->open();
			if($session->has('parent-model')){
				$returnRoute = [$session->get('parent-model')['route'],'id'=>$session->get('parent-model')['id']];
				$session->remove('parent-model');
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
     * Updates an existing ParentModel model.
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
     * Deletes an existing ParentModel model.
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
     * Finds the ParentModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ParentModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ParentModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
