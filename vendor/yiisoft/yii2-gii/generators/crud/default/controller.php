<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
<?php
$tablesArray = [];
foreach($generator->generateRelations() as $relTable){
	$tableName = $relTable[0];
	if(!in_array($tableName,$tablesArray)){
		echo "use app\\models\\".ucfirst($relTable[0]).";\n";
		$tablesArray[] = $tableName;
	}
}
?>


/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
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
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex()
    {
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {<?php
		
		// Generate providers for child models
		foreach($generator->generateRelations() as $relTable){
			$relTableName = Inflector::camel2id($relTable[0],"_");
			$relTableSchema = Yii::$app->db->getSchema()->getTableSchema($relTableName);
			if($generator->isHasManyRelation($relTableSchema,$generator->getTableSchema())){
				//Create new Active Data provider
				$dpVar = "\n\t\t\$".$relTable[0]."Provider";
				echo $dpVar." = new ActiveDataProvider([\n";
				echo "\t\t\t'query'=>".ucfirst($relTable[0])."::find()->where(['".$relTable[1]."'=>\$id])\n";
				echo "\t\t]);\n";

				// Check when a child record has been submitted through modal and save it
				if($generator->useModalEntries){
					$modelName = ucfirst($relTable[0]);
					$columnName = $relTable[1];
					echo "\n\t\tif(in_array('".$modelName."',array_keys(Yii::\$app->request->post()))){\n";
					echo "\t\t\t\$child = new ".$modelName.";\n";
					echo "\t\t\t\$child->attributes = Yii::\$app->request->post('".$modelName."');\n";
					echo "\t\t\tif(empty(\$child->name)){\n";
					echo "\t\t\t\t\$child->name = '".$modelName." #'.strval(".$modelName."::find()->where(['".$columnName."'=>".$actionParams."])->count()+1);\n";
					echo "\t\t\t}\n";
					echo "\t\t\tif(!(\$child->save())){\n";
					echo "\t\t\t\tthrow new \yii\web\HttpException(404, 'Could not save child model');\n";
					echo "\t\t\t}\n";
					echo "\t\t}\n";
				}
				
			}else{
				$var = "\$".$relTable[0]."Model";
				echo "\n\t\t".$var." = new ".ucfirst($relTable[0])."();\n";				
			}

		}
		echo "\n";
	?>
	
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),<?php
				// Pass the child model providers generated above
				foreach($generator->generateRelations() as $relTable){
					$relTableName = Inflector::camel2id($relTable[0],"_");
					$relTableSchema = Yii::$app->db->getSchema()->getTableSchema($relTableName);
					if($generator->isHasManyRelation($relTableSchema,$generator->getTableSchema())){					
						$dpVar = $relTable[0]."Provider";
						echo "\n\t\t\t'".$dpVar."' => \$".$dpVar.",";
					}else{
						$var = $relTable[0]."Model";
						echo "\n\t\t\t"."'".$var."' => \$".$var.",";
					}
				}
				echo "\n";
			?>
        ]);
    }
    
    
<?php
/** 
 * 	Additional actions to be used for creating child records.
 * 	They are invoked in the controller's view action and set a session variable 
 * 	so that the child controller's action create can return to this
 * 	model's view. 
 */
if(!$generator->useModalEntries){
	$rels = $generator->generateRelations();
	if(count($rels) > 0){
		echo "// These are functions to set session parameters for referenced tables\n";
		foreach($rels as $relTable){
			$functionName = "Add".ucfirst($relTable[0]);
			$sessionVar = Inflector::camel2id($relTable[0]);//lcfirst($functionName);
			
			$route = Inflector::camel2id($relTable[0]);
			if($route == 'user' && class_exists('mdm\admin\models\User')) $route = 'admin/'.$route;

			$sessionVal = "['route'=>'".lcfirst($modelClass)."/view','id'=>\$id]";

			echo "\n\tpublic function action".$functionName."(\$id)\n";
			echo "\t{\n";
			echo "\t\t\$session = Yii::\$app->session;\n";
			echo "\t\t\$session->open();\n";
			echo "\t\t\$session->set('".$sessionVar."',".$sessionVal.");\n";
			echo "\t\t\$session->close();\n";
			echo "\t\treturn \$this->redirect(['".$route."/create','".$relTable[1]."'=>\$id]);\n";
			//echo "\t\t;\n";
			echo "\t}\n\n";
		}
	}
}
?> 

<?php
/** 
 * 	Additional actions to be used for creating child records.
 * 	Specific for child records in a one-to-one relationship.
 * 
 * 	They are invoked in the controller's view action and set a session variable 
 * 	so that the child controller's action create can return to this
 * 	model's view. 
 */
foreach($generator->generateRelations() as $relTable){
	$refColumn = $relTable[1];
	$refName = ucfirst($relTable[0]);
	$refTableName = Inflector::camel2id($relTable[0],"_");
	$refTableSchema = Yii::$app->db->getSchema()->getTableSchema($refTableName);
	$refModel = preg_replace("/([I][dD])\b/","",Inflector::variablize($relTable[0]));

	$sessionVar = Inflector::camel2id($relTable[0]);
	$route = Inflector::camel2id($relTable[0]);
	if($route == 'user' && class_exists('mdm\admin\models\User')) $route = 'admin/'.$route;
	$sessionVal = "['route'=>'".Inflector::camel2id($modelClass)."/view','id'=>\$id]";
	
	// Check if it is a One-To-One relationship
	if(!$generator->isHasManyRelation($refTableSchema,$generator->getTableSchema())){
		$functionName = "Add".ucfirst($relTable[0]);
		echo "\n\tpublic function action".$functionName."(\$id)\n";
		echo "\t{\n";
		echo "\t\t\$session = Yii::\$app->session;\n";
		echo "\t\t\$session->open();\n";
		echo "\t\t\$session->set('".$sessionVar."',".$sessionVal.");\n";
		echo "\t\t\$session->close();\n";
		echo "\t\treturn \$this->redirect(['".$route."/create','".$refColumn."'=>\$id]);\n";
		//echo "\t\t;\n";
		echo "\t}\n\n";
	}
}
?> 
    
    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new <?= $modelClass ?>();

		$returnRoute = [];
		
		<?php
		$fks = $generator->getForeignKeysList();
		if(count($fks) > 0){
			echo "// Get foreign keys values if set through request parameters";
			foreach($fks as $columnName => $tableName){
				$route = Inflector::camel2id(Inflector::id2camel($tableName,"_"));			
				if($route == 'user' && class_exists('mdm\admin\models\User')) $route = 'admin/'.$route;
				echo "\n\t\tif(isset(Yii::\$app->request->queryParams['".$columnName."'])){\n";
				echo "\t\t\t\$model->".$columnName." = Yii::\$app->request->queryParams['".$columnName."'];\n";
				//echo "\t\t\t\$returnRoute = ['".$route."/view', 'id'=>\$model->".$columnName."]; \n";
				echo "\t\t}\n";
			}
		}
		?>
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			<?php $thisTableName = Inflector::camel2id($modelClass); ?>		
			//Check for session data
			$session = Yii::$app->session;
			$session->open();
			if($session->has('<?= $thisTableName ?>')){
				$returnRoute = [$session->get('<?= $thisTableName ?>')['route'],'id'=>$session->get('<?= $thisTableName ?>')['id']];
				$session->remove('<?= $thisTableName ?>');
			}
			$session->close();

			if(!empty($returnRoute)){
				return $this->redirect($returnRoute);
            }else{
				return $this->redirect(['view', <?= $urlParams ?>]);
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
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', <?= $urlParams ?>]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        $this->findModel(<?= $actionParams ?>)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return <?=                   $modelClass ?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
