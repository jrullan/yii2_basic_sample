<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Children');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="child-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Child'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
            	'class' => yii\grid\DataColumn::className(),
            	'attribute' => 'name',
            	'format' => 'html',
            	'label' => 'Name',
            	'value' => function($model,$key,$index,$column){
            		return yii\helpers\HTML::a($model->name,['child/view','id'=>$model->id]);
            	},
            ],
            'description:ntext',
            [
            	'class' => yii\grid\DataColumn::className(),
            	'attribute' => 'parent.name',
            	'format' => 'html',
            	'label' => 'Parent_model (parent_id)',
            	'value' => function($model,$key,$index,$column){
            		return !empty($model->parent->name) ? yii\helpers\HTML::a($model->parent->name,['parent-model/view','id'=>$model->parent->id]) : '<span class="not-set">(not set)</span>';
            	},
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
