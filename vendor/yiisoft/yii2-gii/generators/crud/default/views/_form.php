<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
<?php
	//Add required use lines for each related model to use
	//in the dropdown lists
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
?>
/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        /*echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";*/
        echo "<?php\n" . $generator->generateActiveField($attribute) . "\n?>\n\n";
    }
} ?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
