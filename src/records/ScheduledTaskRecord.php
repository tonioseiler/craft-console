<?php

namespace furbo\craftschedule\records;

use craft\db\ActiveRecord;

class ScheduledTaskRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%craftschedule_scheduled_tasks}}';
    }
}
