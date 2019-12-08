<?php

use Phinx\Migration\AbstractMigration;

class Languages extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE languages
            (
                id INT auto_increment,
                code VARCHAR(6) NOT NULL,
                englishName VARCHAR(50) NOT NULL,
                nativeName VARCHAR(50) NOT NULL,
                CONSTRAINT languages_pk PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;        
        ");

        $this->query("
            ALTER TABLE languages
                ADD CONSTRAINT uc_code UNIQUE (code); 
        ");
    }

    public function down()
    {
        $this->query("
            DROP TABLE languages;
        ");
    }
}
