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

    public function getProjectByProjectId(string $projectId)
    {
        return Project::find()->id($projectId)->one();
    }

    public function updateFunding(Project $project, float $amount)
    {
        $project->funded += $amount;
        \Craft::$app->elements->saveElement($project, false);
    }
}
