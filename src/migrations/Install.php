<?php
namespace madebythink\fundraising\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp(): bool
    {
        // Projects Table
        if (!$this->db->tableExists('{{%fundraising_projects}}')) {
            $this->createTable('{{%fundraising_projects}}', [
                'id' => $this->primaryKey(),
                'title' => $this->string(255)->notNull(),
                'subtitle' => $this->string(255),
                'projectId' => $this->string(100)->notNull()->unique(),
                'goal' => $this->decimal(14,2)->notNull()->defaultValue(0),
                'funded' => $this->decimal(14,2)->notNull()->defaultValue(0),
                'startDate' => $this->dateTime()->notNull(),
                'endDate' => $this->dateTime()->notNull(),
                'description' => $this->mediumText(),
                'heroImage' => $this->string(255),
                'mediaGallery' => $this->text(), // JSON of asset IDs or something similar
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }

        $this->addForeignKey(null, '{{%fundraising_projects}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);

        // Donations Table
        if (!$this->db->tableExists('{{%fundraising_donations}}')) {
            $this->createTable('{{%fundraising_donations}}', [
                'id' => $this->primaryKey(),
                'projectId' => $this->integer()->notNull(),
                'donorName' => $this->string(255),
                'amount' => $this->decimal(14,2)->notNull(),
                'comment' => $this->text(),
                'publicComment' => $this->boolean()->defaultValue(false),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);

            $this->addForeignKey(null, '{{%fundraising_donations}}', ['projectId'], '{{%fundraising_projects}}', ['id'], 'CASCADE', 'CASCADE');
        }

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%fundraising_donations}}');
        $this->dropTableIfExists('{{%fundraising_projects}}');
        return true;
    }
}
