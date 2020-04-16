<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class HomeworkController extends BaseController
{
    public function checkListAction(Request $request, $status)
    {
        $user = $this->getUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $status = $request->query->get('status', 'reviewing');
        $keywordType = $request->query->get('keywordType', 'nickname');
        $keyword = $request->query->get('keyword', '');

        $teacherCourses = $this->getCourseMemberService()->findTeacherMembersByUserId($user['id']);
        $courseIds = ArrayToolkit::column($teacherCourses, 'courseId');

        $conditions = array(
            'status' => $status,
            'type' => 'homework',
            'courseIds' => $courseIds,
        );

        if (!empty($courseIds) && 'courseTitle' == $keywordType) {
            $likeCourseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keyword);
            $likeCourseSetIds = ArrayToolkit::column($likeCourseSets, 'id');
            $likeCourses = $this->getCourseService()->findCoursesByCourseSetIds($likeCourseSetIds);
            $likeCourseIds = ArrayToolkit::column($likeCourses, 'id');
            $conditions['courseIds'] = array_intersect($conditions['courseIds'], $likeCourseIds);
        }

        $courses = $this->getCourseService()->findCoursesByIds(array_values($conditions['courseIds']));

        if ('nickname' == $keywordType && $keyword) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $orderBy = $status == 'reviewing' ? array('endTime' => 'ASC') : array('checkedTime' => 'DESC');

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($paperResults, 'userId');
        $userIds = array_merge($userIds, ArrayToolkit::column($paperResults, 'checkTeacherId'));
        $users = $this->getUserService()->findUsersByIds($userIds);

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');
        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $activityIds = ArrayToolkit::column($paperResults, 'lessonId');
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        return $this->render('my/homework/check-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'users' => $users,
            'status' => $status,
            'testpapers' => $testpapers,
            'tasks' => $tasks,
            'keyword' => $keyword,
            'keywordType' => $keywordType,
        ));
    }

    public function listAction(Request $request, $status)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $courseIds = ArrayToolkit::column(
            $this->getCourseMemberService()->searchMembers(array('userId' => $user['id']), array(), 0, PHP_INT_MAX),
            'courseId'
        );
        if (empty($courseIds)) {
            return $this->render('my/homework/my-homework-list.html.twig', array(
                'answerRecords' => array(),
                'status' => $status,
            ));
        }
        $activities = ArrayToolkit::index(
            $this->getActivityService()->search(array('courseIds' => $courseIds, 'mediaType' => 'homework'), array(), 0, PHP_INT_MAX),
            'mediaId'
        );
        $homeworkActivities = ArrayToolkit::index(
            $this->getHomeworkActivityService()->findByIds(array_keys($activities)),
            'answerSceneId'
        );
        if (empty(array_keys($homeworkActivities))) {
            return $this->render('my/homework/my-homework-list.html.twig', array(
                'answerRecords' => array(),
                'status' => $status,
            ));
        }

        $conditions = array(
            'answer_scene_ids' => array_keys($homeworkActivities),
            'user_id' => $user['id'],
            'status' => $status,
        );

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            array('begin_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (empty($answerRecords)) {
            return $this->render('my/homework/my-homework-list.html.twig', array(
                'answerRecords' => array(),
                'status' => $status,
            ));
        }

        $answerReports = ArrayToolkit::index(
            $this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')),
            'id'
        );
        $tasks = ArrayToolkit::index(
            $this->getTaskService()->findTasksByActivityIds(ArrayToolkit::column($activities, 'id')),
            'activityId'
        );
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id'));

        return $this->render('my/homework/my-homework-list.html.twig', array(
            'homeworkActivities' => $homeworkActivities,
            'answerReports' => $answerReports,
            'answerRecords' => $answerRecords,
            'paginator' => $paginator,
            'courses' => $courses,
            'assessments' => $assessments,
            'tasks' => $tasks,
            'activities' => $activities,
            'status' => $status,
        ));
    }

    protected function _getHomeworkDoTime($homeworkResults)
    {
        $homeworkIds = ArrayToolkit::column($homeworkResults, 'testId');
        $homeworkIdCount = array_count_values($homeworkIds);
        $time = 1;
        $homeworkId = 0;

        foreach ($homeworkResults as $key => $homeworkResult) {
            if ($homeworkId == $homeworkResult['testId']) {
                --$time;
            } else {
                $homeworkId = $homeworkResult['testId'];
                $time = $homeworkIdCount[$homeworkResult['testId']];
            }

            $homeworkResults[$key]['seq'] = $time;
        }

        return $homeworkResults;
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }
}
