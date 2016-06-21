<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal; //Added to support creating related models in Modal form.

// Tables pointed to by this model fks
// Tables that have fks that point to this model primary key
use app\models\Child;
use app\models\ParentData;


/* @var $this yii\web\View */
/* @var $model app\models\ParentModel */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Parent Models'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parent-model-view">

<!-- This is the flash message div for creation of a new record of this model -->
<?php
	$session = Yii::$app->session;
		if($session->hasFlash('parent-model-flash')){
		echo Html::beginTag('div',['class'=>'alert alert-success alert-dismissable', 'role'=>'alert']);
			echo $session->getFlash('parent-model-flash');
		echo Html::endTag('div');
	}
?>

<!-- This section add the flash message divs for each possible child created -->
<?php
	if($session->hasFlash('child-flash')){
		echo Html::beginTag('div',['class'=>'alert alert-success alert-dismissable', 'role'=>'alert']);
			echo $session->getFlash('child-flash');
		echo Html::endTag('div');
	}
	if($session->hasFlash('parent-data-flash')){
		echo Html::beginTag('div',['class'=>'alert alert-success alert-dismissable', 'role'=>'alert']);
			echo $session->getFlash('parent-data-flash');
		echo Html::endTag('div');
	}
?>
	

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
			            'id',
            'name',
            'description:ntext',
            [
            	'label' => 'Parent_data',
            	'format' => 'HTML',
            	'value' => (null !== ($model->parentData)) ? yii\helpers\HTML::a($model->parentData->name,['parent-data/view','id'=>$model->parentData->id]) : yii\helpers\HTML::a('Add ParentData',['add-parent-data','id'=>$model->id]),
            ],
        ],
    ]) ?>    
</div>


<div class = "crud-generated-children">
<h2>Children</h2>
<?php
	 Modal::begin([
		'toggleButton' => [
			'label' => '<i class="glyphicon glyphicon-plus"></i> Add Children',
			'class' => 'btn btn-success',
			'title' => 'Creating New Child',
		],
		'closeButton' => [
			'label' => 'Close',
			'class' => 'btn btn-danger btn-sm pull-right',
		],
		'size' => 'modal-lg',
	]);
	$childModel = new Child;
	$childModel->parent_id = $model->id;
	echo $this->render('/child/create', ['model' => $childModel,'noBreadcrumbs'=>true]);
	Modal::end();
?>
<?= GridView::widget([
	'dataProvider' => $childProvider,
		'columns' => [
		'id',
		[
			'class' => yii\grid\DataColumn::className(),
			'format' => 'html',
			'label' => 'Child',
			'value' => function($model,$key,$index,$column){
				return yii\helpers\HTML::a($model->name,['child/view','id'=>$key]);
			},
		],
	],
]);
?>
</div>
