<?php
namespace madebythink\fundraising\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class ProjectQuery extends ElementQuery
{
    public $projectId;
    public $startDate;
    public $endDate;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('fundraising_projects');

        if ($this->projectId) {
            $this->subQuery->andWhere(Db::parseParam('fundraising_projects.projectId', $this->projectId));
        }

        return parent::beforePrepare();
    }
}
