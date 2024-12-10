<?php
namespace madebythink\fundraising\controllers;

use craft\web\Controller;
use Craft;
use yii\web\Response;
use madebythink\fundraising\Plugin;

class SettingsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $settings = Plugin::getInstance()->getSettings();
        return $this->renderTemplate(Plugin::$pluginHandle . '/settings/index', [
            'settings' => $settings,
        ]);
    }

    public function actionSaveSettings()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $plugin = Craft::$app->getPlugins()->getPlugin('fundraising');

        $settings = $plugin->getSettings();
        $settings->defaultCurrency = $request->getBodyParam('defaultCurrency', 'USD');
        $settings->enablePublicCommentsByDefault = (bool)$request->getBodyParam('enablePublicCommentsByDefault', true);
        $settings->defaultGoalCurrencySymbol = $request->getBodyParam('defaultGoalCurrencySymbol', '$');

        if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes())) {
            Craft::$app->getSession()->setNotice("Settings saved.");
        } else {
            Craft::$app->getSession()->setError("Could not save settings.");
        }

        return $this->redirectToPostedUrl();
    }
}
