<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "parent_data".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $parent_model_id
 *
 * @property ParentModel $parentModel
 */
class ParentData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parent_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['parent_model_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['parent_model_id'], 'unique'],
            [['parent_model_id'], 'exist', 'skipOnError' => true, 'targetClass' => ParentModel::className(), 'targetAttribute' => ['parent_model_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'parent_model_id' => Yii::t('app', 'Parent Model ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentModel()
    {
        return $this->hasOne(ParentModel::className(), ['id' => 'parent_model_id']);
    }

	/**
	 *	Autogenerates name field if not filled.
	 *
	 */
	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if(empty($this->name)){
				$number = $this->find()->count();
				$this->name = "Parent_data #".strval($number+1);
			}
			return true;
		} else {
			return false;
		}
	} 


}
