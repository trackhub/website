<?php

use Phinx\Migration\AbstractMigration;

// escape html in track description(bg|en)
class TrackDescriptionHtmlEscape extends AbstractMigration
{
    public function up()
    {
        $trackSql = "SELECT * FROM track WHERE description_en IS NOT NULL OR description_bg IS NOT NULL";
        foreach ($this->fetchAll($trackSql) as $trackData) {
            $bg = htmlspecialchars($trackData['description_bg']);
            $en = htmlspecialchars($trackData['description_en']);

            $this->query('
                UPDATE track
                SET description_en = "' . $en . '", description_bg = "' . $bg . '"
                WHERE id = "' . $trackData['id'] . '"
            ');
        }
    }
}
