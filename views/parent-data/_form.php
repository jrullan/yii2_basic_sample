<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\ParentModel;
/* @var $this yii\web\View */
/* @var $model app\models\ParentData */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="parent-data-form">

    <?php $form = ActiveForm::begin(); ?>

<?php
	echo $form->field($model, 'name')->textInput(['maxlength' => true])
?>

<?php
	echo $form->field($model, 'description')->textarea(['rows' => 6])
?>

<?php
	// Dropdown list for column: parent_model_id
	$list = yii\helpers\ArrayHelper::map(ParentModel::find()->all(),'id','name');
	if(!empty($list)){
		echo $form->field($model,'parent_model_id')->dropDownList($list,['prompt'=>'--Select parent_model_id--']);
	}else{
		echo 'To generate a dropdown list automatically the foreign table should have an \'id\' primary key and a \'name\' column or \'username\' column if the foreign table is user';
	}

?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
