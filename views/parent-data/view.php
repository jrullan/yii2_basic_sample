<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal; //Added to support creating related models in Modal form.

// Tables pointed to by this model fks
use app\models\ParentModel;
// Tables that have fks that point to this model primary key


/* @var $this yii\web\View */
/* @var $model app\models\ParentData */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Parent Datas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parent-data-view">

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
            	'label' => 'Parent_model (parent_model_id)',
            	'format' => 'HTML',
            	'value' => !empty($model->parentModel->name) ? yii\helpers\HTML::a($model->parentModel->name,['parent-model/view','id'=>$model->parentModel->id]) : '<span class="not-set">(not set)</span>',
            ],
        ],
    ]) ?>    
</div>


<div class = "crud-generated-children">
</div>
