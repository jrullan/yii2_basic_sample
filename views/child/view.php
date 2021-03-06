<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal; //Added to support creating related models in Modal form.

// Tables pointed to by this model fks
use app\models\ParentModel;
// Tables that have fks that point to this model primary key
use app\models\Grandchild;


/* @var $this yii\web\View */
/* @var $model app\models\Child */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Children'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="child-view">

<!-- This is the flash message div for creation of a new record of this model -->
<?php
	$session = Yii::$app->session;
		if($session->hasFlash('child-flash')){
		echo Html::beginTag('div',['class'=>'alert alert-success alert-dismissable', 'role'=>'alert']);
			echo $session->getFlash('child-flash');
		echo Html::endTag('div');
	}
?>

<!-- This section add the flash message divs for each possible child created -->
<?php
	if($session->hasFlash('grandchild-flash')){
		echo Html::beginTag('div',['class'=>'alert alert-success alert-dismissable', 'role'=>'alert']);
			echo $session->getFlash('grandchild-flash');
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
            	'label' => 'Parent_model (parent_id)',
            	'format' => 'HTML',
            	'value' => !empty($model->parent->name) ? yii\helpers\HTML::a($model->parent->name,['parent-model/view','id'=>$model->parent->id]) : '<span class="not-set">(not set)</span>',
            ],
        ],
    ]) ?>    
</div>


<div class = "crud-generated-children">
<h2>Grandchildren</h2>
<?php
	 Modal::begin([
		'toggleButton' => [
			'label' => '<i class="glyphicon glyphicon-plus"></i> Add Grandchildren',
			'class' => 'btn btn-success',
			'title' => 'Creating New Grandchild',
		],
		'closeButton' => [
			'label' => 'Close',
			'class' => 'btn btn-danger btn-sm pull-right',
		],
		'size' => 'modal-lg',
	]);
	$childModel = new Grandchild;
	$childModel->child_id = $model->id;
	echo $this->render('/grandchild/create', ['model' => $childModel,'noBreadcrumbs'=>true]);
	Modal::end();
?>
<?= GridView::widget([
	'dataProvider' => $grandchildProvider,
		'columns' => [
		'id',
		[
			'class' => yii\grid\DataColumn::className(),
			'format' => 'html',
			'label' => 'Grandchild',
			'value' => function($model,$key,$index,$column){
				return yii\helpers\HTML::a($model->name,['grandchild/view','id'=>$key]);
			},
		],
	],
]);
?>
</div>
