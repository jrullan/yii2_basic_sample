<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "child".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $parent_id
 *
 * @property ParentModel $parent
 * @property Grandchild[] $grandchildren
 */
class Child extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'child';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['parent_id'], 'required'],
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => ParentModel::className(), 'targetAttribute' => ['parent_id' => 'id']],
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
            'parent_id' => Yii::t('app', 'Parent ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ParentModel::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrandchildren()
    {
        return $this->hasMany(Grandchild::className(), ['child_id' => 'id']);
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
				$this->name = "Child #".strval($number+1);
			}
			return true;
		} else {
			return false;
		}
	} 


}
