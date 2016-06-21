<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Parent Datas');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parent-data-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Parent Data'), ['create'], ['class' => 'btn btn-success']) ?>
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
            		return yii\helpers\HTML::a($model->name,['parent-data/view','id'=>$model->id]);
            	},
            ],
            'description:ntext',
            [
            	'class' => yii\grid\DataColumn::className(),
            	'attribute' => 'parentModel.name',
            	'format' => 'html',
            	'label' => 'Parent_model (parent_model_id)',
            	'value' => function($model,$key,$index,$column){
            		return !empty($model->parentModel->name) ? yii\helpers\HTML::a($model->parentModel->name,['parent-model/view','id'=>$model->parentModel->id]) : '<span class="not-set">(not set)</span>';
            	},
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
