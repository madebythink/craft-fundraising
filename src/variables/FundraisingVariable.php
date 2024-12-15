<?php
namespace madebythink\fundraising\variables;

use Craft;
use madebythink\fundraising\elements\Project;
use madebythink\fundraising\elements\db\ProjectQuery;
use madebythink\fundraising\models\Donation;

class FundraisingVariable
{
    public function projects(array $criteria = []): ProjectQuery
    {
        //return Craft::$app->plugins->getPlugin('craft-fundraising')->projects;
        return Craft::configure(Project::find(), $criteria);
    }

    public function donations()
    {
        return Craft::$app->plugins->getPlugin('craft-fundraising')->donations;
        //return Craft::configure(Donation::find(), $criteria);
    }
}