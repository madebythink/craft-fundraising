<?php
namespace madebythink\fundraising;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

use madebythink\fundraising\elements\Project;
use madebythink\fundraising\fields\ProjectField;
use madebythink\fundraising\services\ProjectsService;
use madebythink\fundraising\services\DonationsService;
use madebythink\fundraising\variables\FundraisingVariable;

class Fundraising extends Plugin
{
    public bool $hasCpSection = true;
    
    public string $schemaVersion = '1.0.0';

    public static $pluginHandle = 'fundraising';

    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'projects' => ProjectsService::class,
            'donations' => DonationsService::class,
        ]);

        // Register element type and fields
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Project::class;
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = ProjectField::class;
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $e) {
            $variable = $e->sender;
            $variable->set('fundraising', FundraisingVariable::class);
        });

        // SecurePay event listener (example placeholder)
        // Event::on(
        //     \securepay\plugin\SecurePay::class,
        //     'EVENT_AFTER_DONATION',
        //     function($event) {
        //         $donationData = $event->donationData;
        //         $this->donations->recordDonation($donationData);
        //     }
        // );

        Craft::$app->onInit(function() {
            // Register CP URL rules
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_CP_URL_RULES,
                function(RegisterUrlRulesEvent $event) {
                    $event->rules[self::$pluginHandle] = self::$pluginHandle . '/dashboard/index';
                    $event->rules[self::$pluginHandle . '/dashboard'] = self::$pluginHandle . '/dashboard/index';
                    $event->rules[self::$pluginHandle . '/projects'] = self::$pluginHandle . '/projects/index';
                    $event->rules[self::$pluginHandle . '/projects/edit'] = self::$pluginHandle . '/projects/edit';
                    $event->rules[self::$pluginHandle . '/projects/edit/<projectId:\d+>'] = self::$pluginHandle . '/projects/edit';
                    $event->rules[self::$pluginHandle . '/settings'] = self::$pluginHandle . '/settings/index';
                }
            );

            // (Optional) Register site URL rules if needed
            // Event::on(
            //     UrlManager::class,
            //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            //     function(RegisterUrlRulesEvent $event) {
            //         // Define any front-end routes here if needed.
            //     }
            // );
        });
    }

    public function getCpNavItem(): ?array
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = \Craft::t('fundraising', 'Fundraising');
        $navItem['url'] = 'fundraising/dashboard';

        $navItem['subnav'] = [
            'dashboard' => ['label' => \Craft::t('fundraising', 'Dashboard'), 'url' => 'fundraising/dashboard'],
            'projects'  => ['label' => \Craft::t('fundraising', 'Projects'), 'url' => 'fundraising/projects'],
            'settings'  => ['label' => \Craft::t('fundraising', 'Settings'), 'url' => 'fundraising/settings'],
        ];

        return $navItem;
    }

    protected function createSettingsModel(): \craft\base\Model
    {
        return new \craft\base\Model([
            'defaultCurrency' => 'USD',
            'enablePublicCommentsByDefault' => true,
            'defaultGoalCurrencySymbol' => '$',
        ]);
    }

    public function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate(
            'fundraising/settings/index',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}