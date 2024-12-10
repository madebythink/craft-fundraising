<?php
namespace madebythink\fundraising\controllers;

use craft\web\Controller;
use madebythink\fundraising\elements\Project;
use Craft;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProjectsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $projects = Project::find()->all();
        return $this->renderTemplate('fundraising/projects/index', [
            'projects' => $projects,
        ]);
    }

    public function actionEdit(int $projectId = null): Response
    {
        $project = $projectId ? Project::findOne($projectId) : new Project();

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        return $this->renderTemplate('fundraising/projects/_edit', [
            'project' => $project,
        ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $projectId = $request->getBodyParam('projectId');
        $project = $projectId ? Project::findOne($projectId) : new Project();

        $project->title = $request->getBodyParam('title');
        $project->subtitle = $request->getBodyParam('subtitle');
        $project->projectId = $request->getBodyParam('projectIdField');
        $project->goal = (float)$request->getBodyParam('goal');
        $project->startDate = new \DateTime($request->getBodyParam('startDate'));
        $project->endDate = new \DateTime($request->getBodyParam('endDate'));
        $project->description = $request->getBodyParam('description');
        $project->heroImage = $request->getBodyParam('heroImage');
        $project->mediaGallery = json_encode($request->getBodyParam('mediaGallery'));

        if (Craft::$app->getElements()->saveElement($project)) {
            Craft::$app->getSession()->setNotice("Project saved.");
            return $this->redirectToPostedUrl($project);
        } else {
            Craft::$app->getSession()->setError("Could not save project.");
        }

        return $this->renderTemplate('fundraising/projects/_edit', [
            'project' => $project,
        ]);
    }
}
