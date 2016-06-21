<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ParentData */

$this->title = Yii::t('app', 'Create Parent Data');

if(!($noBreadcrumbs)){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Parent Datas'), 'url' => ['index']];
	$this->params['breadcrumbs'][] = $this->title;
}

?>
<div class="parent-data-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
