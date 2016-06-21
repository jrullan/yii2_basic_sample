<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "grandchild".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $child_id
 *
 * @property Child $child
 */
class Grandchild extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'grandchild';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['child_id'], 'required'],
            [['child_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['child_id'], 'exist', 'skipOnError' => true, 'targetClass' => Child::className(), 'targetAttribute' => ['child_id' => 'id']],
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
            'child_id' => Yii::t('app', 'Child ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(Child::className(), ['id' => 'child_id']);
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
				$this->name = "Grandchild #".strval($number+1);
			}
			return true;
		} else {
			return false;
		}
	} 


}
