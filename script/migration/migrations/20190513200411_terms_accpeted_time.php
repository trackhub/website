<?php

use Phinx\Migration\AbstractMigration;

class TermsAccpetedTime extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE user ADD terms_accepted DATETIME DEFAULT NULL
        ");
    }

    public function down()
    {
        throw new \Exception("not impl.");
    }
}
