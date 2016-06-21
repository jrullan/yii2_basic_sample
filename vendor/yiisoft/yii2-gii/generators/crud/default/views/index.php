<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            // '" . $name . "',\n";
        }
    }
} else {
	// A schema was found. Expected for all Active Records.
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        
		//The following modification was done to account for foreign key relationships with other models.
        $generatedValue = "";
        $useSmartForeignKeys = $generator->enableSmartFK && $generator->isForeignKey($column->name);
        if($useSmartForeignKeys){
			$generatedValue = $generator->generateGridViewForeignKey($column->name);
			//echo "            " . $generatedValue . ",\n";
		}else{
			//$generatedValue = "'".$column->name."'";
		}
		
    
        //-----------------------------------------------------------------------------------------------
        
        
        if (++$count < 6 || $useSmartForeignKeys) {
			//Use a link for "name" column
			if($column->name == "name"){
				$space = "            ";
				$detail = "";
				$detail .= $space."[\n";
				$detail .= $space."\t'class' => yii\\grid\\DataColumn::className(),\n";
				$detail .= $space."\t'attribute' => '".$column->name."',\n";
				$detail .= $space."\t'format' => 'html',\n";
				$detail .= $space."\t'label' => 'Name',\n";
				$detail .= $space."\t'value' => function(\$model,\$key,\$index,\$column){\n";
				$detail .= $space."\t\treturn yii\\helpers\\HTML::a(\$model->name,['".Inflector::camel2id($generator->getControllerID())."/view','id'=>\$model->id]);\n";
				$detail .= $space."\t},\n";
				$detail .= $space."],\n";
				echo $detail;
			}else{
				//For all other columns...
				if($useSmartForeignKeys){
					echo "            " . $generatedValue . ",\n";
				}else{
					echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
				}
			}
        } else {
            echo "            // '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>
</div>
