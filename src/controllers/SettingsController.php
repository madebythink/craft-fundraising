<?php
namespace madebythink\fundraising\controllers;

use craft\web\Controller;
use Craft;
use yii\web\Response;
use madebythink\fundraising\Fundraising;

class SettingsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $settings = Fundraising::getInstance()->getSettings();
        return $this->renderTemplate(Fundraising::$pluginHandle . '/settings/index', [
            'settings' => $settings,
            'pluginHandle' => Fundraising::$pluginHandle,
            'errors' => $settings->getErrors(),
        ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $plugin = Fundraising::getInstance();

        $settings = $plugin->getSettings();
        $settings->defaultCurrency = $request->getBodyParam('defaultCurrency', 'AUD');
        $settings->enablePublicCommentsByDefault = (bool)$request->getBodyParam('enablePublicCommentsByDefault', false);
        $settings->defaultGoalCurrencySymbol = $request->getBodyParam('defaultGoalCurrencySymbol', '$');

        if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes())) {
            Craft::$app->getSession()->setNotice("Settings saved.");
        } else {
            Craft::$app->getSession()->setError("Could not save settings.");
        }

        return $this->redirectToPostedUrl();
    }
}
