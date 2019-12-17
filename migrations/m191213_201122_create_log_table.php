<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%log}}`.
 */
class m191213_201122_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%log}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(),
            'date' => $this->bigInteger(),
            'url' => $this->text(),
            'os' => $this->string(),
            'x_bit' => $this->string(),
            'browser' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%log}}');
    }
}
