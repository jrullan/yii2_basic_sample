<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal; //Added to support creating related models in Modal form.

<?php
	//Add required use lines for foreign keys related model to use
	//in the dropdown lists
	echo "// Tables pointed to by this model fks\n";
	$foreignKeys = $generator->getForeignKeysList();
	$includeModels = array_values($foreignKeys);
	$useArray = [];
	foreach($includeModels as $model){
		if(!in_array($model,$useArray)){
			if($model == 'user'){
				if(class_exists("mdm\admin\models\User")){
						echo "use mdm\\admin\\models\\User;\n";
				}else{
						echo "use app\\models\\User;\n";
				}
			}else{
				echo "use app\\models\\".Inflector::id2camel($model,"_").";\n";
			}
			$useArray[]=$model;
		}
	}
	
	
	//Add required use lines for models related inverse of foreign keys
	echo "// Tables that have fks that point to this model primary key\n";
	$relatedModels = $generator->generateRelations();
	foreach($relatedModels as $relModel){
		//echo "<pre>".print_r($relModel,true)."</pre>\n";
		$relModelName = $relModel[0];
		$relColumn = $relModel[1];
		if($relModelName == 'user'){
			if(class_exists("mdm\admin\models\User")){
				echo "use mdm\\admin\\models\\User;\n";
			}else{
				echo "use app\\models\\User;\n";
			}
		}else{
			echo "use app\\models\\".ucfirst($relModelName).";\n";
		}
	}
?>


/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Delete') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
			<?php
			//First add the table columns
			if (($tableSchema = $generator->getTableSchema()) === false) {
				foreach ($generator->getColumnNames() as $name) {
					echo "            '" . $name . "',\n";
				}
			} else {
				foreach ($generator->getTableSchema()->columns as $column) {
					$format = $generator->generateColumnFormat($column);
					
					$generatedValue = "";
					
					if($generator->enableSmartFK && $generator->isForeignKey($column->name)){			
						$generatedValue = $generator->generateDetailViewForeignKey($column->name);
						echo "            " . $generatedValue . ",\n";
					}else{
						$generatedValue = $column->name;
						echo "            '" . $generatedValue . ($format === 'text' ? "" : ":" . $format) . "',\n";
					}
				}
			}
			// Second add the related one-to-one columns, if any
			foreach($generator->generateRelations() as $relTable){
				$refColumn = $relTable[1];
				$refName = ucfirst($relTable[0]);
				$refTableName = Inflector::camel2id($relTable[0],"_");
				$refTableSchema = Yii::$app->db->getSchema()->getTableSchema($refTableName);
				$refModel = preg_replace("/([I][dD])\b/","",Inflector::variablize($relTable[0]));
				if(!$generator->isHasManyRelation($refTableSchema,$generator->getTableSchema())){
					
					$createLink = "";
					if($generator->useModalEntries){
						$createLink .= "Modal::begin([\n";
						$createLink .= "\t\t'toggleButton' => [\n";
						$createLink .= "\t\t\t'label' => '<i class=\"glyphicon glyphicon-plus\"></i> Add ".Inflector::pluralize($refTableName)."',\n";
						$createLink .= "\t\t\t'class' => 'btn btn-success',\n";
						$createLink .= "\t\t\t'title' => 'Creating New ".$refTableName."',\n";
						$createLink .= "\t\t],\n";
						$createLink .= "\t\t'closeButton' => [\n";
						$createLink .= "\t\t\t'label' => 'Close',\n";
						$createLink .= "\t\t\t'class' => 'btn btn-danger btn-sm pull-right',\n";
						$createLink .= "\t\t],\n";
						$createLink .= "\t\t'size' => 'modal-lg',\n";
						$createLink .= "\t]);\n";
						$createLink .= "\t\$childModel = new ".$refTableName.";\n";
						$createLink .= "\t\$childModel->".$refColumn." = \$model->id;\n";
						$createLink .= "\techo \$this->render('/".Inflector::camel2id($refTableName)."/create', ['model' => \$childModel,'noBreadcrumbs'=>true]);\n";
						$createLink .= "\tModal::end();\n";
						$createLink .= "";
					}else{
						$createLink .= "yii\\helpers\\HTML::a('Add ".ucfirst($refModel)."',['add-".Inflector::camel2id($refName)."','id'=>\$model->id]),\n";
					}					
					
					
					// Create the Detail View attribute entry for this child record
					$space = "            ";
					$detail = $space."[\n";
					$detail .= $space."\t'label' => '".ucfirst($refTableName)."',\n";
					$detail .= $space."\t'format' => 'HTML',\n";
					//use terniary operator to avoid a crash if foreign key is not set
					$routeName = Inflector::camel2id(Inflector::variablize($refTableName));
					if($refTableName == 'user'){
						$foreignNameColumn = $this->defaultUsernameColumn;
						//If Yii2-Admin extension is installed prepend the user route with admin so: admin/user is the correct route.
						if(class_exists('mdm\admin\models\User')) $routeName = 'admin/'.$routeName;
					}
					$detail .= $space."\t'value' => ";
					//$detail .= "!empty(\$model->".$refModel."->".$refColumn.") ? ";
					$detail .= "(null !== (\$model->".$refModel.")) ? ";
					$detail .= "yii\\helpers\\HTML::a(\$model->".$refModel."->name,['".$routeName."/view','id'=>\$model->".$refModel."->id])";
					//$detail .= " : ".$createLink;
					$detail .= " : yii\\helpers\\HTML::a('Add ".ucfirst($refModel)."',['add-".Inflector::camel2id($refName)."','id'=>\$model->id]),\n";
					//$detail .= " : '<span class=\"not-set\">(not set)</span>',\n";
					$detail .= $space."],\n";
					echo $detail;
				}
			}
			?>
        ],
    ]) ?>    
</div>


<div class = "crud-generated-children">
<?php
	foreach($generator->generateRelations() as $relTable){
		$refColumn = $relTable[1];
		$tableName = ucfirst($relTable[0]);
		$route = substr($generator->modelClass,strrpos($generator->modelClass,'\\')+1);
		$route = Inflector::camel2id($route)."/".Inflector::camel2id("Add".$tableName);
		
		// Check if any of the fks that point to this table is unique
		// if it's not unique then it is a one-to-many relationship
		$relTableName = Inflector::camel2id($relTable[0],"_");
		$tableSchema = Yii::$app->db->getSchema()->getTableSchema($relTableName);
		if($generator->isHasManyRelation($tableSchema,$generator->getTableSchema())){
			//Generates related model of one-to-many type
			$dpVar = "\$".$relTable[0]."Provider";
			
			//$route = substr($generator->modelClass,strrpos($generator->modelClass,'\\')+1);
			//$route = Inflector::camel2id($route)."/".Inflector::camel2id("Add".$tableName);
			/* echo "<p><?= Html::a(Yii::t('app', 'Add ".$tableName."'), ['".$route."/create','".$refColumn."'=>\$model->id], ['class' => 'btn btn-success']) ?></p>\n";*/
			
			echo "<h2>".Inflector::pluralize($tableName)."</h2>\n";
			
			if($generator->useModalEntries){
				echo "<?php\n\t Modal::begin([\n";
				echo "\t\t'toggleButton' => [\n";
				echo "\t\t\t'label' => '<i class=\"glyphicon glyphicon-plus\"></i> Add ".Inflector::pluralize($tableName)."',\n";
				echo "\t\t\t'class' => 'btn btn-success',\n";
				echo "\t\t\t'title' => 'Creating New ".$tableName."',\n";
				echo "\t\t],\n";
				echo "\t\t'closeButton' => [\n";
				echo "\t\t\t'label' => 'Close',\n";
				echo "\t\t\t'class' => 'btn btn-danger btn-sm pull-right',\n";
				echo "\t\t],\n";
				echo "\t\t'size' => 'modal-lg',\n";
				echo "\t]);\n";
				echo "\t\$childModel = new ".$tableName.";\n";
				echo "\t\$childModel->".$refColumn." = \$model->id;\n";
				echo "\techo \$this->render('/".Inflector::camel2id($tableName)."/create', ['model' => \$childModel,'noBreadcrumbs'=>true]);\n";
				echo "\tModal::end();\n";
				echo "?>\n";
			}else{
				echo "<p>\n<?= Html::a(Yii::t('app', 'Add ".$tableName."'), ['".$route."','id'=>\$model->id], ['class' => 'btn btn-success']) ?></p>\n";
			}	
			
			$columnName = ($tableName == "User") ? $generator->defaultUsernameColumn : $generator->defaultNameColumn;
			echo "<?= GridView::widget([\n";
			echo "\t'dataProvider' => ".$dpVar.",\n";
			echo "\t\t'columns' => [\n";
			echo "\t\t'id',\n";			
			echo "\t\t[\n";
			echo "\t\t\t'class' => yii\\grid\\DataColumn::className(),\n";
			echo "\t\t\t'format' => 'html',\n";
			echo "\t\t\t'label' => '".$tableName."',\n";
			echo "\t\t\t'value' => function(\$model,\$key,\$index,\$column){\n";
			echo "\t\t\t\treturn yii\\helpers\\HTML::a(\$model->".$columnName.",['".Inflector::camel2id($tableName)."/view','id'=>\$key]);\n";
			echo "\t\t\t},\n";
			echo "\t\t],\n";
			echo "\t],\n";
			echo "]);\n";
			echo "?>\n";
		

		}
		/*else{
			//This section is for one-to-one relationships -- UNFINISHED
			echo "<h2>".$tableName."</h2>\n";
			echo "<div class=\"".Inflector::camel2id(StringHelper::basename($relTable[0]))."-form\">\n";
			echo "<?php \$form = ActiveForm::begin(); ?>\n";
			$modelVar = "\$".$relTable[0]."Model";
			//Use a dropdown list instead and assign the relationship
			echo "<?php\n";
			$className = Inflector::id2camel($relTableName);
			$attribute = $refColumn;
			$code = "\t// Dropdown list for column: ".$refColumn."\n";
			$nameColumn = ($relTableName == 'user') ? $generator->defaultUsernameColumn : $generator->defaultNameColumn;
			$code .= "\t\$list = yii\\helpers\\ArrayHelper::map(".$className."::find()->all(),'id','".$nameColumn."');\n";
			$code .= "\tif(!empty(\$list)){\n";
			$code .= "\t\techo \$form->field(".$modelVar.",'".$attribute."')->dropDownList(\$list,['prompt'=>'--Select ".$attribute."--']);\n";
			$code .= "\t}else{\n";
			$code .= "\t\techo 'To generate a dropdown list automatically the foreign table should have an \'id\' primary key and a \'name\' column or \'username\' column if the foreign table is user';\n";
			$code .= "\t}\n";			
			echo $code;
			echo "?>\n";
			echo "<div class=\"form-group\">\n";
			echo "<?= Html::submitButton(".$modelVar."->isNewRecord ? ".$generator->generateString('Create')." : ".$generator->generateString('Update').", ['class' => ".$modelVar."->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>\n";
			echo "</div>\n";
			echo "<?php \$form = ActiveForm::end(); ?>\n";

		}
		*/
	}
?>
</div>
