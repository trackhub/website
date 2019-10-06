<?php

use Phinx\Migration\AbstractMigration;

class Places extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE place (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                lat DOUBLE PRECISION NOT NULL,
                lng DOUBLE PRECISION NOT NULL,
                name_en VARCHAR(255) DEFAULT NULL,
                name_bg VARCHAR(255) DEFAULT NULL,
                `type` TINYINT(1) NOT NULL,
                send_by_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("ALTER TABLE place ADD CONSTRAINT FK_PLACE_send_by_id FOREIGN KEY (send_by_id) REFERENCES `user` (id)");
        $this->query("CREATE INDEX IDX_PLACE_SEND_BY_ID ON place (send_by_id)");
    }

    public function down()
    {
        $this->query("DROP TABLE place");
    }
}
