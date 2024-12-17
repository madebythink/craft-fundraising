<?php
namespace madebythink\fundraising\elements;

use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use madebythink\fundraising\elements\db\ProjectQuery;
use madebythink\fundraising\records\ProjectRecord;
use madebythink\fundraising\Fundraising;
use Craft;
use craft\helpers\Db;
use craft\elements\Asset;

class Project extends Element
{
    public ?string $subtitle = null;
    public ?string $projectId = null;
    public float $goal = 0.0;
    public float $funded = 0.0;
    public ?\DateTime $startDate = null;
    public ?\DateTime $endDate = null;
    public ?string $description = null;
    protected ?array $_heroImage = null; // JSON with single asset ID
    protected ?array $_mediaGallery = null; // JSON of asset IDs

    // Magic getter to resolve dynamically
    public function __get($name)
    {
        switch ($name) {
            case 'heroImage':
                return $this->getHeroImage();
            case 'mediaGallery':
                return $this->getMediaGallery();
        }

        return parent::__get($name);
    }

    // Magic setter to allow dynamic property setting
    public function __set($name, $value)
    {
        switch ($name) {
            case 'heroImage':
                // Decode JSON if a string is provided
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }
                $this->_heroImage = is_array($value) ? $value : null;
                break;

            case 'mediaGallery':
                // Decode JSON if a string is provided
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }
                $this->_mediaGallery = is_array($value) ? $value : null;
                break;

            default:
                parent::__set($name, $value);
        }
    }


    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function displayName(): string
    {
        return Craft::t(Fundraising::$pluginHandle, 'Project');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t(Fundraising::$pluginHandle, 'project');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t(Fundraising::$pluginHandle, 'Projects');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t(Fundraising::$pluginHandle, 'projects');
    }

    public static function find(): ElementQueryInterface
    {
        return new ProjectQuery(static::class);
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'projectId' => ['label' => Craft::t('app', 'Project ID')],
            'subtitle' => ['label' => Craft::t('app', 'Subtitle')],
            'goal' => ['label' => Craft::t('app', 'Goal')],
            'funded' => ['label' => Craft::t('app', 'Funded')],
            'startDate' => ['label' => Craft::t('app', 'Start Date')],
            'endDate' => ['label' => Craft::t('app', 'End Date')],
            'heroImage' => ['label' => Craft::t('app', 'Hero Image')],
            'mediaGallery' => ['label' => Craft::t('app', 'Media Gallery')],
        ];
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('craft-fundraising', 'All Projects')
            ]
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'projectId' => Craft::t('app', 'Project ID'),
            'goal' => Craft::t('app', 'Goal'),
            'funded' => Craft::t('app', 'Funded'),
            'startDate' => Craft::t('app', 'Start Date'),
            'endDate' => Craft::t('app', 'End Date'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'subtitle', 'projectId'];
    }

    public function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'subtitle':
                return $this->subtitle ? htmlspecialchars($this->subtitle, ENT_QUOTES, 'UTF-8') : '';

            case 'projectId':
                return $this->projectId ?: '';

            case 'goal':
                return $this->goal !== null ? Craft::$app->getFormatter()->asCurrency($this->goal) : '';

            case 'funded':
                return $this->funded !== null ? Craft::$app->getFormatter()->asCurrency($this->funded) : '';

            case 'startDate':
                return $this->startDate ? Craft::$app->getFormatter()->asDatetime($this->startDate, 'short') : '';

            case 'endDate':
                return $this->endDate ? Craft::$app->getFormatter()->asDatetime($this->endDate, 'short') : '';

            case 'heroImage':
                if (is_array($this->_heroImage) && !empty($this->_heroImage[0])) {
                    $assetId = $this->_heroImage[0];
                    $asset = \craft\elements\Asset::find()->id($assetId)->one();
                    if ($asset) {
                        $thumbUrl = $asset->getUrl(['width' => 50, 'height' => 50]);
                        return '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($asset->title, ENT_QUOTES, 'UTF-8') . '" style="max-width:50px;max-height:50px;">';
                    }
                }
                return '';

            case 'mediaGallery':
                if (is_array($this->_mediaGallery) && !empty($this->_mediaGallery)) {
                    $assets = \craft\elements\Asset::find()->id($this->_mediaGallery)->all();
                    $html = '';
                    foreach ($assets as $asset) {
                        $thumbUrl = $asset->getUrl(['width' => 30, 'height' => 30]);
                        $html .= '<img src="' . $thumbUrl . '" alt="' . htmlspecialchars($asset->title, ENT_QUOTES, 'UTF-8') . '" style="max-width:30px;max-height:30px;margin-right:5px;">';
                    }
                    return $html;
                }
                return '';

            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    public function getHeroImageAsset(): ?\craft\elements\Asset
    {
        if (is_array($this->_heroImage) && !empty($this->_heroImage[0])) {
            return \craft\elements\Asset::find()->id($this->_heroImage[0])->one();
        }

        return null;
    }

    public function getMediaGalleryAssets(): array
    {
        if (is_array($this->_mediaGallery) && !empty($this->_mediaGallery)) {
            return \craft\elements\Asset::find()->id($this->_mediaGallery)->all();
        }

        return [];
    }

    /**
     * Returns an ElementQuery for the hero image.
     * 
     * This allows you to do `project.heroImage.one()` in Twig to get the single asset.
     */
    public function getHeroImage(): \craft\elements\db\AssetQuery
    {
        // If heroImage is empty or not an array, return an empty query
        $ids = (is_array($this->_heroImage) && !empty($this->_heroImage)) ? $this->_heroImage : [0]; 
        return \craft\elements\Asset::find()->id($ids);
    }

    /**
     * Returns an ElementQuery for the mediaGallery.
     * 
     * This allows `project.mediaGallery.all()` in Twig to get all related assets.
     */
    public function getMediaGallery(): \craft\elements\db\AssetQuery
    {
        $ids = (is_array($this->_mediaGallery) && !empty($this->_mediaGallery)) ? $this->_mediaGallery : [0];
        return \craft\elements\Asset::find()->id($ids);
    }

    public function getIsNew(): bool
    {
        return $this->id === null;
    }

    public function getCpEditUrl(): ?string
    {
        return 'fundraising/projects/edit/' . $this->id;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['title', 'projectId', 'goal', 'startDate', 'endDate'], 'required'];
        $rules[] = ['projectId', 'validateProjectIdUnique'];
        return $rules;
    }

    public function validateProjectIdUnique($attribute, $params, $validator)
    {
        $query = ProjectRecord::find()->where(['projectId' => $this->projectId]);

        // If editing an existing project, exclude it from uniqueness check
        if (!$this->getIsNew()) {
            $query->andWhere(['not', ['id' => $this->id]]);
        }

        if ($query->exists()) {
            $this->addError($attribute, Craft::t('fundraising', 'Project ID is already in use.'));
        }
    }

    public function beforeSave(bool $isNew): bool
    {
        if (!parent::beforeSave($isNew)) {
            return false;
        }
        return true;
    }

    public function afterSave(bool $isNew): void
    {
        parent::afterSave($isNew);

        $record = ProjectRecord::findOne($this->id);

        if (!$record) {
            $record = new ProjectRecord();
            $record->id = $this->id;
        }

        $record->title = $this->title;
        $record->subtitle = $this->subtitle;
        $record->projectId = $this->projectId;
        $record->goal = $this->goal;
        $record->funded = $this->funded;
        $record->startDate = Db::prepareDateForDb($this->startDate);
        $record->endDate = Db::prepareDateForDb($this->endDate);
        $record->description = $this->description;
        $record->heroImage = $this->_heroImage;
        $record->mediaGallery = $this->_mediaGallery;

        // Encode the array of Asset IDs if not already encoded.
        $record->heroImage = is_array($this->_heroImage) ? json_encode($this->_heroImage) : $this->_heroImage;
        $record->mediaGallery = is_array($this->_mediaGallery) ? json_encode($this->_mediaGallery) : $this->_mediaGallery;

        $record->save(false);
    }

    public function afterDelete(): void
    {
        parent::afterDelete();

        $record = ProjectRecord::findOne($this->id);

        if ($record) {
            $record->delete();
        }
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    public function getFieldLayout(): null
    {
        return null; // If you have a field layout, define here.
    }

    public function afterPopulate(): void
    {
        parent::afterPopulate();

        // heroImage might be stored as a JSON string if not null
        if ($this->_heroImage && is_string($this->_heroImage)) {
            $decoded = json_decode($this->_heroImage, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->_heroImage = $decoded;
            }
        }

        // mediaGallery might also be stored as JSON
        if ($this->_mediaGallery && is_string($this->_mediaGallery)) {
            $decoded = json_decode($this->_mediaGallery, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->_mediaGallery = $decoded;
            }
        }
    }
}
