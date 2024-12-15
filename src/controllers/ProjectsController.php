<?php
namespace madebythink\fundraising\controllers;

use craft\web\Controller;
use madebythink\fundraising\elements\Project;
use madebythink\fundraising\Fundraising;
use madebythink\fundraising\records\ProjectRecord;
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
            'pluginHandle' => Fundraising::$pluginHandle,
        ]);
    }

    public function actionNew(): Response
    {
        $project = new Project();

        return $this->renderTemplate('fundraising/projects/_edit', [
            'project' => $project,
            'pluginHandle' => Fundraising::$pluginHandle,
        ]);
    }

    public function actionEdit(int $id = null): Response
    {
        //$project = Project::findOne($id);

        // Check what $projectId is
        Craft::info("SAM2 Editing project element ID: $id", __METHOD__);

        // Explicitly find the project by its element ID
        $project = Project::find()->id($id)->one();


        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        return $this->renderTemplate('fundraising/projects/_edit', [
            'project' => $project,
            'pluginHandle' => Fundraising::$pluginHandle,
        ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $projectId = $request->getBodyParam('projectId');
        $project = $projectId ? Project::findOne($projectId) : new Project();

        $project->projectId = $request->getBodyParam('projectIdField');
        $project->title = $request->getBodyParam('title');
        $project->subtitle = $request->getBodyParam('subtitle');
        $project->description = $request->getBodyParam('description');

        $project->goal = (float)$request->getBodyParam('goal');
        $project->startDate = new \DateTime($request->getBodyParam('startDate')['date']);
        $project->endDate = new \DateTime($request->getBodyParam('endDate')['date']);
        
        $project->heroImage = $request->getBodyParam('heroImage', []);
        $project->mediaGallery = $request->getBodyParam('mediaGallery', []);

        if (Craft::$app->getElements()->saveElement($project)) {
            Craft::$app->getSession()->setNotice("Project saved.");
            return $this->redirectToPostedUrl($project);
        } else {
            Craft::$app->getSession()->setError("Could not save project.");
        }

        return $this->renderTemplate('fundraising/projects/_edit', [
            'project' => $project,
            'pluginHandle' => Fundraising::$pluginHandle,
        ]);
    }

    public function actionDelete()
    {
        //$this->requirePermission(Fundraising::PERMISSION_PROJECTS_ADMIN);
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        // Get ID from POST parameter
        $id = $request->getRequiredBodyParam('projectId');

        $project = Project::findOne($id);
        if (!$project) {
            Craft::$app->getSession()->setError('Project not found.');
            return $this->redirectToPostedUrl();
        }

        if ($project && Craft::$app->getElements()->deleteElement($project)) {
            Craft::$app->getSession()->setNotice('Project deleted.');
        } else {
            Craft::$app->getSession()->setError('Could not delete the project.');
        }

        // Redirect to the project list
        return $this->redirectToPostedUrl();
    }

    public function actionCheckProjectId()
    {
        $this->requireAcceptsJson();
        $projectId = Craft::$app->getRequest()->getQueryParam('projectId');

        // Determine if the Project ID already exists and doesn't belong to the Project currently being edited
        $editId = Craft::$app->getRequest()->getQueryParam('editId');

        if ($editId) {
            $exists = ProjectRecord::find()->where(['projectId' => $projectId])->andWhere(['!=', 'id', $editId])->exists();
        } else {
            $exists = ProjectRecord::find()->where(['projectId' => $projectId])->exists();
        }

        return $this->asJson([
            'unique' => !$exists,
            'message' => $exists ? Craft::t('fundraising', 'Project ID is already in use.') : '',
        ]);
    }
}
