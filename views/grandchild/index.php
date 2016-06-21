<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Grandchildren');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grandchild-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Grandchild'), ['create'], ['class' => 'btn btn-success']) ?>
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
            		return yii\helpers\HTML::a($model->name,['grandchild/view','id'=>$model->id]);
            	},
            ],
            'description:ntext',
            [
            	'class' => yii\grid\DataColumn::className(),
            	'attribute' => 'child.name',
            	'format' => 'html',
            	'label' => 'Child (child_id)',
            	'value' => function($model,$key,$index,$column){
            		return !empty($model->child->name) ? yii\helpers\HTML::a($model->child->name,['child/view','id'=>$model->child->id]) : '<span class="not-set">(not set)</span>';
            	},
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
