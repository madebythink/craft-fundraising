<?php
namespace madebythink\fundraising;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\services\Fields;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

use madebythink\fundraising\elements\Project;
use madebythink\fundraising\models\Settings;
use madebythink\fundraising\fields\ProjectField;
use madebythink\fundraising\services\ProjectsService;
use madebythink\fundraising\services\DonationsService;
use madebythink\fundraising\variables\FundraisingVariable;

class Fundraising extends Plugin
{
    public bool $hasCpSection = true;
    
    public string $schemaVersion = '1.0.0';

    public static $pluginHandle = 'fundraising';

    const PERMISSION_PROJECTS_ADMIN = 'fundraising-projects-admin';
    const PERMISSION_FORMS_ADMIN = 'fundraising-forms-admin';
    const PERMISSION_SUBMISSIONS_VIEW = 'fundraising-submissions-view';
    const PERMISSION_DONATIONS_VIEW = 'fundraising-donations-view';


    const TRANSLATION_CATEGORY = 'fundraising';

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
            $variable->set(self::$pluginHandle, FundraisingVariable::class);
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
            Event::on(
                View::class,
                View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
                function(RegisterTemplateRootsEvent $event) {
                    $event->roots[self::$pluginHandle] = __DIR__ . '/templates';
                }
            );

            // Register CP URL rules
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_CP_URL_RULES,
                function(RegisterUrlRulesEvent $event) {
                    $event->rules[self::$pluginHandle] = self::$pluginHandle . '/dashboard/index';
                    $event->rules[self::$pluginHandle . '/dashboard'] = self::$pluginHandle . '/dashboard/index';
                    $event->rules[self::$pluginHandle . '/projects'] = self::$pluginHandle . '/projects/index';
                    $event->rules[self::$pluginHandle . '/projects/new'] = self::$pluginHandle . '/projects/new';
                    $event->rules[self::$pluginHandle . '/projects/edit'] = self::$pluginHandle . '/projects/edit';
                    $event->rules[self::$pluginHandle . '/projects/edit/<id:\d+>'] = self::$pluginHandle . '/projects/edit';
                    $event->rules[self::$pluginHandle . '/projects/check-project-id'] = self::$pluginHandle . '/projects/check-project-id';
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

    private function permissions() {
        if (\Craft::$app->getEdition() >= \Craft::Pro) {
            Event::on(
                UserPermissions::class,
                UserPermissions::EVENT_REGISTER_PERMISSIONS,
                function (RegisterUserPermissionsEvent $event) {
                    $permissions = [
                        self::PERMISSION_PROJECTS_ADMIN => ['label' => Craft::t(self::TRANSLATION_CATEGORY, 'Create, modify and delete projects')],
                        self::PERMISSION_FORMS_ADMIN => ['label' => Craft::t(self::TRANSLATION_CATEGORY, 'Create, modify and delete forms')],
                        self::PERMISSION_SUBMISSIONS_VIEW => ['label' => Craft::t(self::TRANSLATION_CATEGORY, 'View submissions')],
                        self::PERMISSION_DONATIONS_VIEW => ['label' => Craft::t(self::TRANSLATION_CATEGORY, 'View donations')],
                    ];

                    $event->permissions[self::$plugin->pluginName] = $permissions;
                }
            );
        }
    }

    public function getCpNavItem(): ?array
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = \Craft::t(self::$pluginHandle, 'Fundraising');
        $navItem['url'] = self::$pluginHandle . '';

        $navItem['subnav'] = [
            self::$pluginHandle . '/dashboard' => ['label' => \Craft::t(self::$pluginHandle, 'Dashboard'), 'url' => self::$pluginHandle . '/dashboard'],
            self::$pluginHandle . '/projects'  => ['label' => \Craft::t(self::$pluginHandle, 'Projects'), 'url' => self::$pluginHandle . '/projects'],
            self::$pluginHandle . '/settings'  => ['label' => \Craft::t(self::$pluginHandle, 'Settings'), 'url' => self::$pluginHandle . '/settings'],
        ];

        return $navItem;
    }

    protected function createSettingsModel(): \craft\base\Model
    {
        return Craft::createObject(Settings::class);
    }

    public function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate(
            self::$pluginHandle . '/settings/index',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}