<?php

use Phinx\Migration\AbstractMigration;

class VersionUser extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE version ADD send_by_id INT DEFAULT NULL, CHANGE file_id file_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', CHANGE track_id track_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', CHANGE name name VARCHAR(255) DEFAULT NULL;
        ");

        $this->query("
            ALTER TABLE version ADD CONSTRAINT FK_BF1CD3C3C3852542 FOREIGN KEY (send_by_id) REFERENCES `user` (id);
        ");
    }

    public function down()
    {
        throw new \Exception("not implemented");
    }
}
