<?php
namespace madebythink\fundraising\variables;

use madebythink\fundraising\elements\Project;
use Craft;

class FundraisingVariable
{
    public function projects()
    {
        return Craft::$app->plugins->getPlugin('craft-fundraising')->projects;
    }

    public function donations()
    {
        return Craft::$app->plugins->getPlugin('craft-fundraising')->donations;
    }
}
