<?php

use Phinx\Migration\AbstractMigration;

class DropFosUserBundle extends AbstractMigration
{
    public function up()
    {
        $this->query("
            DROP INDEX UNIQ_8D93D64992FC23A8 ON user;
            DROP INDEX UNIQ_8D93D649A0D96FBF ON user;
            DROP INDEX UNIQ_8D93D649C05FB297 ON user;
        ");

        $this->query("
            ALTER TABLE user
                DROP COLUMN username_canonical;
            ALTER TABLE user
                DROP COLUMN email_canonical;
            ALTER TABLE user
                DROP COLUMN last_login;
            ALTER TABLE user
                DROP COLUMN salt;
            ALTER TABLE user
                DROP COLUMN password_requested_at;
            ALTER TABLE user
                DROP COLUMN password;
            ALTER TABLE user
                DROP COLUMN confirmation_token;
        ");

        $this->query("
            ALTER TABLE user
                CHANGE COLUMN username nickname VARCHAR(180) NOT NULL
        ");
    }

    public function down()
    {
        $this->query("
            ALTER TABLE user
                CHANGE COLUMN nickname username VARCHAR(180) NOT NULL;
        ");

        $this->query("
            ALTER TABLE user
                ADD username_canonical VARCHAR(180) NOT NULL;
            ALTER TABLE user
                ADD email_canonical VARCHAR(180) NOT NULL;
            ALTER TABLE user
                ADD last_login DATETIME DEFAULT NULL;
            ALTER TABLE user
                ADD salt VARCHAR(255) DEFAULT NULL;
            ALTER TABLE user
                ADD password_requested_at DATETIME DEFAULT NULL;
            ALTER TABLE user
                ADD password VARCHAR(255) NOT NULL;
            ALTER TABLE user
                ADD confirmation_token VARCHAR(180) DEFAULT NULL;
        ");

        $this->query("
            CREATE UNIQUE INDEX UNIQ_8D93D64992FC23A8 ON user(username_canonical);
            CREATE UNIQUE INDEX UNIQ_8D93D649A0D96FBF ON user(email_canonical);
            CREATE UNIQUE INDEX UNIQ_8D93D649C05FB297 ON user(confirmation_token);
        ");
    }
}
