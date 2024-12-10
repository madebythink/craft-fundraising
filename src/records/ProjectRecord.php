<?php
namespace madebythink\fundraising\records;

use craft\db\ActiveRecord;

class ProjectRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%fundraising_projects}}';
    }
}
