<?php

namespace common\modules\survey\models;

use Yii;

/**
 * This is the model class for table "survey_answer".
 *
 * @property string $survey_answer_id
 * @property string $survey_answer_name
 * @property string $survey_answer_descr
 * @property string $survey_answer_class
 * @property string $survey_answer_comment
 * @property integer $survey_answer_question_id
 * @property integer $survey_answer_sort
 * @property integer $survey_answer_points
 * @property boolean $survey_answer_show_descr
 *
 * @property SurveyQuestion $question
 * @property SurveyUserAnswer[] $userAnswers
 */
class SurveyAnswer extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'survey_answer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['survey_answer_descr'], 'string'],
            [['survey_answer_question_id', 'survey_answer_sort', 'survey_answer_points'], 'integer'],
            [['survey_answer_question_id', 'survey_answer_sort', 'survey_answer_points'], 'filter', 'filter' => 'intval'],
            [['survey_answer_show_descr'], 'boolean'],
            [['survey_answer_show_descr'], 'filter', 'filter' => 'boolval'],
            [['survey_answer_name'], 'string', 'max' => 100],
            [['survey_answer_name'], 'required'],
            [['survey_answer_class', 'survey_answer_comment'], 'string', 'max' => 255],
            [['survey_answer_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => SurveyQuestion::className(), 'targetAttribute' => ['survey_answer_question_id' => 'survey_question_id']],
        ];
    }

    public function afterDelete()
    {
        $question = $this->question;
        if (!empty($question)) {
            $answersCount = $question->getAnswers()->count();
            if ($answersCount == 0) {
                //prevent deleting last answer
                $question->link('answers', (new SurveyAnswer(['survey_answer_sort' => 0])));
            }
        }

        return parent::afterDelete();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'survey_answer_id' => Yii::t('survey', 'Answer ID'),
            'survey_answer_name' => Yii::t('survey', 'Answer'),
            'survey_answer_descr' => Yii::t('survey', 'Detailed description'),
            'survey_answer_show_descr' => Yii::t('survey', 'Show detailed description'),
            'survey_answer_class' => Yii::t('survey', 'Class'),
            'survey_answer_comment' => Yii::t('survey', 'Comment'),
            'survey_answer_question_id' => Yii::t('survey', 'Question ID'),
            'survey_answer_points' => Yii::t('survey', 'Points'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(SurveyQuestion::className(), ['survey_question_id' => 'survey_answer_question_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAnswers()
    {
        return $this->hasMany(SurveyUserAnswer::className(), ['survey_user_answer_answer_id' => 'survey_answer_id']);
    }
}
