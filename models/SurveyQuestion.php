<?php

namespace common\modules\survey\models;

use Yii;

/**
 * This is the model class for table "survey_question".
 *
 * @property integer $survey_question_id
 * @property string $survey_question_name
 * @property string $survey_question_descr
 * @property integer $survey_question_type
 * @property integer $survey_question_survey_id
 * @property boolean $survey_question_can_skip
 * @property boolean $survey_question_show_descr
 * @property boolean $survey_question_is_scorable
 *
 * @property SurveyAnswer[] $answers
 * @property Survey $survey
 * @property SurveyType $questionType
 * @property SurveyUserAnswer[] $userAnswers
 */
class SurveyQuestion extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'survey_question';
    }

    public function changeDefaultValuesOnTypeChange(){
        /** @var SurveyQuestion $question */
        $question = $this;
        $oldType = $question->getOldAttribute('survey_question_type');
        $newType = $question->getAttribute('survey_question_type');
        if ($oldType === $newType){
            return true;
        }

        if ($newType === SurveyType::TYPE_SLIDER){
            $question->unlinkAll('answers', true);
            for ($i = 1; $i <= 2; ++$i) {
                $answer = new SurveyAnswer();
                $answer->survey_answer_sort = $i;
                $answer->survey_answer_name = ($i === 1) ? 0 : 100;
                $question->link('answers', $answer);
            }
        }else if($oldType === SurveyType::TYPE_SLIDER){
            $question->unlinkAll('answers', true);
            for ($i = 1; $i <= 2; ++$i) {
                $answer = new SurveyAnswer();
                $answer->survey_answer_sort = $i;
                $question->link('answers', $answer);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['survey_question_descr'], 'string'],
            [['survey_question_type', 'survey_question_survey_id'], 'integer'],
            [['survey_question_type'], 'filter', 'filter' => 'intval'],
            [['survey_question_can_skip', 'survey_question_show_descr', 'survey_question_is_scorable'], 'boolean'],
            [['survey_question_can_skip', 'survey_question_show_descr', 'survey_question_is_scorable'], 'filter', 'filter' => 'boolval'],
            [['survey_question_name'], 'string', 'max' => 45],
            [['survey_question_name'], 'required'],
            [['survey_question_survey_id'], 'exist', 'skipOnError' => true, 'targetClass' => Survey::className(), 'targetAttribute' => ['survey_question_survey_id' => 'survey_id']],
            [['survey_question_type'], 'exist', 'skipOnError' => true, 'targetClass' => SurveyType::className(), 'targetAttribute' => ['survey_question_type' => 'survey_type_id']],
        ];
    }

    public function beforeSave($insert)
    {
        //scorable questions
        if (!$insert && !in_array($this->survey_question_type, [
            SurveyType::TYPE_MULTIPLE,
            SurveyType::TYPE_ONE_OF_LIST,
            SurveyType::TYPE_DROPDOWN
        ])) {
            if ($this->survey_question_is_scorable){
                $this->survey_question_is_scorable = false;
            }
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        $this->survey_question_type = SurveyType::TYPE_MULTIPLE; //multiple choice
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'survey_question_id' => Yii::t('survey', 'Question ID'),
            'survey_question_name' => Yii::t('survey', 'Survey Question Name'),
            'survey_question_descr' => Yii::t('survey', 'Detailed description'),
            'survey_question_type' => Yii::t('survey', 'Question Type'),
            'survey_question_survey_id' => Yii::t('survey', 'Survey ID'),
            'survey_question_can_skip' => Yii::t('survey', 'Can be skipped'),
            'survey_question_show_descr' => Yii::t('survey', 'Show detailed description'),
            'survey_question_is_scorable' => Yii::t('survey', 'Score this question'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(SurveyAnswer::className(), ['survey_answer_question_id' => 'survey_question_id'])
            ->orderBy(['survey_answer_sort' => SORT_ASC, 'survey_answer_id' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['survey_id' => 'survey_question_survey_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestionType()
    {
        return $this->hasOne(SurveyType::className(), ['survey_type_id' => 'survey_question_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAnswers()
    {
        return $this->hasMany(SurveyUserAnswer::className(), ['survey_user_answer_question_id' => 'survey_question_id']);
    }
}
