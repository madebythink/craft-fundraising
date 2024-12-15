<?php
namespace madebythink\fundraising\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use Craft;


class ProjectQuery extends ElementQuery
{
    public $projectId;
    public $startDate;
    public $endDate;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('fundraising_projects');
        $this->subQuery->andWhere(Db::parseParam('fundraising_projects.id', $this->id));

        $this->query->select([
            'fundraising_projects.subtitle',
            'fundraising_projects.projectId',
            'fundraising_projects.goal',
            'fundraising_projects.funded',
            'fundraising_projects.startDate',
            'fundraising_projects.endDate',
            'fundraising_projects.description',
            'fundraising_projects.heroImage',
            'fundraising_projects.mediaGallery',
        ]);
        
        // Log the subquery to CraftCMS logs
        Craft::info('ProjectQuery Subquery: ' . $this->subQuery->createCommand()->rawSql, __METHOD__);

        return parent::beforePrepare();
    }
}
