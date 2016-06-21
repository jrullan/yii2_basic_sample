<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Grandchild */

$this->title = Yii::t('app', 'Create Grandchild');

if(!($noBreadcrumbs)){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Grandchildren'), 'url' => ['index']];
	$this->params['breadcrumbs'][] = $this->title;
}

?>
<div class="grandchild-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
