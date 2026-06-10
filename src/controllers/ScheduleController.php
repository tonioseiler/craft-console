<?php

namespace furbo\craftschedule\controllers;

use Craft;
use craft\web\Controller;
use furbo\craftschedule\CraftSchedule;
use yii\web\Response;

class ScheduleController extends Controller
{
    protected array|bool|int $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $this->requireAdmin();
        return $this->asJson(['tasks' => CraftSchedule::$plugin->scheduleService->getAll()]);
    }

    public function actionToggle(): Response
    {
        $this->requireAdmin();
        $this->requirePostRequest();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');
        return $this->asJson(CraftSchedule::$plugin->scheduleService->toggle((int) $id));
    }
}
