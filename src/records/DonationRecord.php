<?php
namespace madebythink\fundraising\records;

use craft\db\ActiveRecord;

class DonationRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%fundraising_donations}}';
    }
}
