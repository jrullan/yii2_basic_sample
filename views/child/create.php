<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Child */

$this->title = Yii::t('app', 'Create Child');

if(!($noBreadcrumbs)){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Children'), 'url' => ['index']];
	$this->params['breadcrumbs'][] = $this->title;
}

?>
<div class="child-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
