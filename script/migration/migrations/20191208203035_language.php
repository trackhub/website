<?php

use Phinx\Migration\AbstractMigration;

class Language extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE language
            (
                id INT auto_increment,
                code VARCHAR(6) NOT NULL,
                name_en VARCHAR(50) NOT NULL,
                name VARCHAR(50) NOT NULL,
                CONSTRAINT language_pk PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;        
        ");

        $this->query("
            ALTER TABLE language
                ADD CONSTRAINT uc_code UNIQUE (code); 
        ");
    }

    public function down()
    {
        $this->query("
            DROP TABLE language;
        ");
    }
}
