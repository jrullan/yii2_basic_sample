<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\helpers\ArrayHelper;

/**
 * Generates CRUD
 *
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property boolean|\yii\db\TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $modelClass;
    public $controllerClass;
    public $viewPath;
    public $baseControllerClass = 'yii\web\Controller';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';
    
    //Smart Foreign Keys
    public $enableSmartFK = true;
    public $useModalEntries = true;
    public $defaultNameColumn = 'name';
    public $defaultUsernameColumn = 'username'; 
    
    /**
     * @var boolean whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = true;


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'CRUD Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
            operations for the specified data model.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
            [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
            [['controllerClass', 'searchModelClass'], 'validateNewClass'],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['modelClass'], 'validateModelClass'],
            [['enableI18N', 'enablePjax', 'enableSmartFK', 'useModalEntries'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            [['viewPath','defaultNameColumn','defaultUsernameColumn'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
            'enablePjax' => 'Enable Pjax',
            'enableSmartFK' => 'Enable Smart Foreign Keys',
            'useModalEntries' => 'Use Modal Data Entry for Child Models',
            'defaultNameColumn'=> 'Smart Foreign Keys: Default name column',
            'defaultUsernameColumn'=>'Smart Foreign Keys: Default name column for user table',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase with an uppercase first letter. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
                widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
                sorting, filtering and pagination without page refreshing.',
            'enableSmartFK' => 'This indicates if the generator will try to use the default name column or username column specified below
				for columns with foreing keys. If checked, the CRUD views will try to show the related column name instead of the primary key
				value for the _form, gridview and listview widgets.',
			'useModalEntries' => 'This enables usage of the Modal Widget to do the data entry for child models',
			'defaultNameColumn' => 'The default name column for foreign key relationships is <code>\'name\'</code>. To use a different column, specify it here.', 
			'defaultUsernameColumn' => 'This is the name of the user class name column to be used in the dropdown list. By default the generator will try
				to use <code>\'username\'</code>, assuming that the Yii2-Admin extension is used. If it fails it will try to use <code>\'name\'</code> as the column name.', 
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'indexWidgetType', 'defaultNameColumn', 'defaultUsernameColumn']);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }

        return $files;
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID()
    {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);

        return Inflector::camel2id($class);
    }

    /**
     * @return string the controller view path
     */
    public function getViewPath()
    {
        if (empty($this->viewPath)) {
            return Yii::getAlias('@app/views/' . $this->getControllerID());
        } else {
            return Yii::getAlias($this->viewPath);
        }
    }

    public function getNameAttribute()
    {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();

        return $pk[0];
    }


	/**
	 * Get available foreign keys to later generate dropdown lists
	 * This code also gets the table name of the foreign key.
	 * @param string $attribute the column name being checked for foreign key
	 * @return string[] array of "column names"=>"tableName" pairs that have a foreign key.
	 */
	public function getForeignKeysList(){
		$tableSchema = $this->getTableSchema();
		$fks = $tableSchema->foreignKeys;
		$fkeys = array();
		$tableName=$tableSchema->name;
		if(is_array($fks)){
			foreach($fks as $fk) {
				$tableName = array_shift($fk); //take out the first element it is the table name
				foreach(array_keys($fk) as $colname) {
					$fkeys[$colname] = $tableName;
				}
			}
		}
		return $fkeys;	
	}

	/**
	 * Get a list of all foreign keys in a table
	 * @param TableSchema $tableSchema
	 * @return string[] array of foreign keys found in this format: "table_name.ref_table.column_name"
	 * to capture only the column name you could use substr($fk,strpos($fk,".")+1)
	 */
	public function getFks($tableSchema){
		$fks = $tableSchema->foreignKeys;
		$fksList = []; //first item is the table name
		foreach($fks as $fk){
			$refTable = array_shift($fk);
			foreach(array_keys($fk) as $key=>$val){
				$fksList[] = $refTable.".".$val;
			}
		}
		return $fksList;
	}
	

	/**
	 * Checks if the $attribute column has a foreign key.
	 * @param string $attribute the column name being checked for foreign key
	 * @return boolean indicating if the column name has a foreign key.
	 */
	public function isForeignKey($attribute){
		$foreignKeys = $this->getForeignKeysList();
		return in_array($attribute, array_keys($foreignKeys));
	}

	/**
	 * Generates a list of tables that have foreign keys pointing to this 
	 * table primary key.
	 * @return array[][2] $relations table name and column name
	 */
	public function generateRelations(){
		$tableName = $this->getTableSchema()->name;
		$db = Yii::$app->get('db', false);
		$relations = [];
		//$relations[] = $tableName;
		foreach ($db->getSchema()->getTableSchemas() as $refTable) {
			if(!empty($refTable->foreignKeys)){
				
				//Each table that has foreign keys...
				foreach($refTable->foreignKeys as $fk){
					$name = '';
					if($fk[0] == $tableName){
						array_shift($fk);
						$name = lcfirst(Inflector::id2camel($refTable->name,"_"));
						foreach($fk as $k => $val){
							$relations[] = [$name,$k];
						}
					}
				}
			}
		}
		return $relations;
	}

	/**
	 * Generates the dropdown lists in _form.php for foreign keys relationships.
	 * @param string $attribute the column name being checked for foreign key
	 * @return string Code to implement the dropdown list with the foreign key values.
	 */
	public function generateFkDropdown($attribute){
		$foreignKeys = $this->getForeignKeysList();
		$tableName = $foreignKeys[$attribute];
		$className = Inflector::id2camel($tableName,"_");
		$fkeys = array_keys($foreignKeys);
		
		//If the name of the column is found in the foreign keys list, then generate a dropdown
		//using the relationship of the tables.
		$code = "\t// Dropdown list for column: ".$attribute."\n";
		$nameColumn = ($tableName == 'user') ? $this->defaultUsernameColumn : $this->defaultNameColumn;
		$code .= "\t\$list = yii\\helpers\\ArrayHelper::map(".$className."::find()->all(),'id','".$nameColumn."');\n";
		$code .= "\tif(!empty(\$list)){\n";
		$code .= "\t\techo \$form->field(\$model,'".$attribute."')->dropDownList(\$list,['prompt'=>'--Select ".$attribute."--']);\n";
		$code .= "\t}else{\n";
		$code .= "\t\techo 'To generate a dropdown list automatically the foreign table should have an \'id\' primary key and a \'name\' column or \'username\' column if the foreign table is user';\n";
		$code .= "\t}\n";
		return $code;
	}


	/**
	 * Generates the contents to be used in the DetailView widget
	 * for the columns that have a foreign key.
	 * @param string $attribute the column name being checked for foreign key
	 * @return string Code to implement the value of the DetailView widget.
	 */
	public function generateDetailViewForeignKey($attribute){
		$foreignKeys = $this->getForeignKeysList();
		$tableName = $foreignKeys[$attribute];
		$refModel = preg_replace("/([I][dD])\b/","",Inflector::variablize($attribute)); // remove the Id from the end of the name
		$routeName = Inflector::camel2id(Inflector::variablize($tableName)); // Turns a FirstClass into first-class, which is the way routes are implemented.
		$columnName = ucfirst($attribute);
		$foreignNameColumn = $this->defaultNameColumn;
		
		$space = "            ";
		$detail = "";
		$detail .= "[\n";
		$detail .= $space."\t'label' => '".ucfirst($tableName)." (".$attribute.")"."',\n";
		$detail .= $space."\t'format' => 'HTML',\n";
		//use terniary operator to avoid a crash if foreign key is not set
		if($tableName == 'user'){
			$foreignNameColumn = $this->defaultUsernameColumn;
			//If Yii2-Admin extension is installed prepend the user route with admin so: admin/user is the correct route.
			if(class_exists('mdm\admin\models\User')) $routeName = 'admin/'.$routeName;
		}
		$detail .= $space."\t'value' => ";
		$detail .= "!empty(\$model->".$refModel."->".$foreignNameColumn.") ? ";
		$detail .= "yii\\helpers\\HTML::a(\$model->".$refModel."->".$foreignNameColumn.",['".$routeName."/view','id'=>\$model->".$refModel."->id])";
		$detail .= " : '<span class=\"not-set\">(not set)</span>',\n";
		$detail .= $space."]";
		return $detail;
	}

	/**
	 * Generates the contents to be used in the GridView widget
	 * for the columns that have a foreign key.
	 */
	public function generateGridViewForeignKey($attribute){
		$foreignKeys = $this->getForeignKeysList();
		$tableName = $foreignKeys[$attribute];
		$refModel = preg_replace("/([I][dD])\b/","",Inflector::variablize($attribute)); // remove the Id from the end of the name
		$routeName = Inflector::camel2id(Inflector::variablize($tableName)); // Turns a FirstClass into first-class, which is the way routes are implemented.
		$columnName = ucfirst($attribute);
		$foreignNameColumn = $this->defaultNameColumn;
		
		$space = "            ";
		$detail = "";
		$detail .= "[\n";
		$detail .= $space."\t'class' => yii\\grid\\DataColumn::className(),\n";
		$attr = "";
		if($tableName == "user"){
			$foreignNameColumn = $this->defaultUsernameColumn;
			//If Yii2-Admin extension is installed prepend the user route with admin so: admin/user is the correct route.
			if(class_exists('mdm\admin\models\User')) $routeName = 'admin/'.$routeName;
		}
		$attr = $refModel.".".$foreignNameColumn;
		$detail .= $space."\t'attribute' => '".$attr."',\n";
		$detail .= $space."\t'format' => 'html',\n";
		$detail .= $space."\t'label' => '".ucfirst($tableName)." (".$attribute.")"."',\n";
		$detail .= $space."\t'value' => function(\$model,\$key,\$index,\$column){\n";
		$detail .= $space."\t\treturn !empty(\$model->".$refModel."->".$foreignNameColumn.") ? ";
		$detail .= "yii\\helpers\\HTML::a(\$model->".$refModel."->".$foreignNameColumn.",['".$routeName."/view','id'=>\$model->".$refModel."->id])";
		$detail .= " : '<span class=\"not-set\">(not set)</span>';\n";
		$detail .= $space."\t},\n";
		$detail .= $space."]";
		return $detail;
	}


    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute)
    {
		$foreignKeys = $this->getForeignKeysList();
		$fkeys = array_keys($foreignKeys);

        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\techo \$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\techo \$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];
		
		// Create elements based on the data type of the column
        if ($column->phpType === 'boolean') {
            return "\techo \$form->field(\$model, '$attribute')->checkbox()";
        } elseif ($column->type === 'text') {
            return "\techo \$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }

            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\techo \$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])";

              //Generates dropdown lists for the form foreign key relationships      
            } elseif(($this->enableSmartFK) && (in_array($column->name, $fkeys))){ 
				return $this->generateFkDropdown($attribute);

			} elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\techo \$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\techo \$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
            }
        }
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        } elseif ($column->type === 'text') {
            return 'ntext';
        } elseif (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        } elseif (stripos($column->name, 'email') !== false) {
            return 'email';
        } elseif (stripos($column->name, 'url') !== false) {
            return 'url';
        } else {
            return 'text';
        }
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules()
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes()
    {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels()
    {
        /* @var $model \yii\base\Model */
        $model = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions()
    {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params[] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params[] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        } else {
            return '$' . implode(', $', $pks);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return boolean|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * @return array model column names
     */
    public function getColumnNames()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            /* @var $model \yii\base\Model */
            $model = new $class();

            return $model->attributes();
        }
    }
    
    /**
     * Determines if relation is of has many type
     *
     * @param TableSchema $table
     * @param array $fks <-- These are the column names that have a fk
     * @return boolean
     * @since 2.0.5
     */
    public function isHasManyRelation($relTable, $thisTable)
    {
		$relTableName = $relTable->name;
		$relFkeys = $this->getFks($relTable);
		
		// Get a list of remote fks that point to this table
		$fks = [];
		$thisTableName = $this->getTableSchema()->name;
		foreach($relFkeys as $relKey){
			$arr = explode(".",$relKey); //[0] is referenced table name, [1] is fk column
			if($arr[0] === $thisTableName) $fks[] = $arr[1];
		}
					
        $uniqueKeys = [$relTable->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, Yii::$app->db->getSchema()->findUniqueIndexes($relTable));
        } catch (NotSupportedException $e) {
            // ignore
        }
        foreach ($uniqueKeys as $uniqueKey) {
            if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                return false;
            }
        }
        return true;
    }    
}
