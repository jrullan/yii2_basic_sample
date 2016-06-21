<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ParentModel */

$this->title = Yii::t('app', 'Create Parent Model');

if(!($noBreadcrumbs)){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Parent Models'), 'url' => ['index']];
	$this->params['breadcrumbs'][] = $this->title;
}

?>
<div class="parent-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
