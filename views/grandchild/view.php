<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal; //Added to support creating related models in Modal form.

// Tables pointed to by this model fks
use app\models\Child;
// Tables that have fks that point to this model primary key


/* @var $this yii\web\View */
/* @var $model app\models\Grandchild */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Grandchildren'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grandchild-view">

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
            	'label' => 'Child (child_id)',
            	'format' => 'HTML',
            	'value' => !empty($model->child->name) ? yii\helpers\HTML::a($model->child->name,['child/view','id'=>$model->child->id]) : '<span class="not-set">(not set)</span>',
            ],
        ],
    ]) ?>    
</div>


<div class = "crud-generated-children">
</div>
