<?php

namespace furbo\craftschedule\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp(): bool
    {
        $this->dropOldTable();
        $this->createTables();
        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%craftschedule_scheduled_tasks}}');
        return true;
    }

    private function dropOldTable(): void
    {
        $oldTable = '{{%craftconsole_cronjobs}}';
        if ($this->db->tableExists($oldTable)) {
            $this->dropTable($oldTable);
        }
    }

    private function createTables(): void
    {
        if (!$this->db->tableExists('{{%craftschedule_scheduled_tasks}}')) {
            $this->createTable('{{%craftschedule_scheduled_tasks}}', [
                'id' => $this->primaryKey(),
                'fingerprint' => $this->string(64)->notNull(),
                'command' => $this->string(255)->notNull(),
                'params' => $this->text(),
                'schedule' => $this->string(100)->notNull(),
                'enabled' => $this->boolean()->defaultValue(true),
                'lastRunAt' => $this->dateTime(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, '{{%craftschedule_scheduled_tasks}}', 'fingerprint', true);
            $this->createIndex(null, '{{%craftschedule_scheduled_tasks}}', 'enabled');
        }
    }
}
