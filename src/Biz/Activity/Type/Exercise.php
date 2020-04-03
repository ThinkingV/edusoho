<?php

namespace Biz\Activity\Type;

use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class Exercise extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getExerciseActivityService()->getActivity($targetId);
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getExerciseActivityService()->findActivitiesByIds($targetIds);
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        $answerScene = $this->getAnswerSceneService()->create(array(
            'name' => $fields['name'],
            'limited_time' => 0,
            'do_times' => 0,
            'redo_interval' => 0,
            'need_score' => 0,
            'manual_marking' => 0,
            'start_time' => 0,
        ));

        return $this->getExerciseActivityService()->createActivity(array(
            'sceneId' => $answerScene['id'],
            'drawCondition' => $this->getCondition($fields),
        ));
    }

    public function copy($activity, $config = array())
    {
        $newActivity = $config['newActivity'];
        $exercise = $this->get($activity['mediaId']);

        $answerScene = $this->getAnswerSceneService()->create(array(
            'name' => $newActivity['title'],
        ));

        return $this->getExerciseActivityService()->createActivity(array(
            'sceneId' => $answerScene['id'],
            'drawCondition' => $exercise['drawCondition'],
        ));
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExercise = $this->get($sourceActivity['mediaId']);
        $exercise = $this->get($activity['mediaId']);

        $this->getAnswerSceneService()->update($exercise['sceneId'], array('name' => $sourceActivity['title']));

        return $this->getExerciseActivityService()->updateActivity($exercise['id'], array(
            'drawCondition' => $sourceExercise['drawCondition'],
        ));
    }

    public function update($targetId, &$fields, $activity)
    {
        $exercise = $this->get($targetId);

        if (!$exercise) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        $filterFields = $this->filterFields($fields);

        $this->getAnswerSceneService()->update($exercise['sceneId'], array('name' => $filterFields['name']));

        return $this->getExerciseActivityService()->updateActivity($exercise['id'], array(
            'drawCondition' => $this->getCondition($filterFields),
        ));
    }

    public function delete($targetId)
    {
        return $this->getExerciseActivityService()->deleteActivity($targetId);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'exercise');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'exercise');

        if (!$result) {
            return false;
        }

        if (!empty($exercise['passedCondition']) && 'submit' === $activity['finishType'] && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'range',
            'itemCount',
            'difficulty',
            'questionTypes',
            'passedCondition',
            'fromCourseId',
            'fromCourseSetId',
            'courseSetId',
            'courseId',
            'lessonId',
            'metas',
            'copyId',
        ));

        $filterFields['courseId'] = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['name'] = empty($filterFields['title']) ? '' : $filterFields['title'];

        if (!empty($fields['finishType'])) {
            $filterFields['passedCondition']['type'] = $fields['finishType'];
        }

        return $filterFields;
    }

    public function getCondition($fields)
    {
        $range = $fields['range'];
        $questionBank = $this->getQuestionBankService()->getQuestionBank($range['bankId']);

        return array(
            'range' => array(
                'bank_id' => empty($questionBank['itemBankId']) ? 0 : $questionBank['itemBankId'],
                'category_ids' => empty($range['categoryIds']) ? array() : explode(',', $range['categoryIds']),
            ),
            'section' => array(
                'conditions' => array(
                    'item_types' => $fields['questionTypes'],
                    'distribution' => empty($fields['difficulty']) ? array() : array($fields['difficulty'] => 100),
                ),
                'item_count' => $fields['itemCount'],
            ),
        );
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->getBiz()->service('Activity:ExerciseActivityService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }
}
