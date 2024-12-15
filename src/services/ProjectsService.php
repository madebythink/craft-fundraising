<?php
namespace madebythink\fundraising\services;

use craft\base\Component;
use madebythink\fundraising\elements\Project;

class ProjectsService extends Component
{
    public function allProjects()
    {
        return Project::find()->all();
    }

    public function getProjectByProjectId(string $id)
    {
        return Project::find()->id($id)->one();
    }

    public function updateFunding(Project $project, float $amount)
    {
        $project->funded += $amount;
        \Craft::$app->elements->saveElement($project, false);
    }
}
