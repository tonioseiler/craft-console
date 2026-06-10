<?php

namespace furbo\craftschedule\utilities;

use Craft;
use craft\base\Utility;

class ScheduleUtility extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('craft-schedule', 'Craft Schedule');
    }

    public static function id(): string
    {
        return 'craft-schedule';
    }

    public static function icon(): ?string
    {
        return 'clock';
    }

    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('craft-schedule/_utilities/schedule');
    }
}
