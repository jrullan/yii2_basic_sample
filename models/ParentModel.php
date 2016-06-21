<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "parent_model".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 *
 * @property Child[] $children
 * @property ParentData $parentData
 */
class ParentModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parent_model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['name'], 'string', 'max' => 45],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Child::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentData()
    {
        return $this->hasOne(ParentData::className(), ['parent_model_id' => 'id']);
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
				$this->name = "Parent_model #".strval($number+1);
			}
			return true;
		} else {
			return false;
		}
	} 


}
