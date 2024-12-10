<?php
namespace madebythink\fundraising\elements;

use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use madebythink\fundraising\elements\db\ProjectQuery;
use madebythink\fundraising\records\ProjectRecord;
use Craft;
use craft\helpers\Db;

class Project extends Element
{
    public ?string $subtitle = null;
    public ?string $projectId = null;
    public float $goal = 0.0;
    public float $funded = 0.0;
    public ?\DateTime $startDate = null;
    public ?\DateTime $endDate = null;
    public ?string $description = null;
    public ?string $heroImage = null; // Store as asset UID or path
    public ?string $mediaGallery = null; // JSON of asset IDs/paths

    public static function displayName(): string
    {
        return Craft::t('craft-fundraising', 'Project');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('craft-fundraising', 'project');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('craft-fundraising', 'Projects');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('craft-fundraising', 'projects');
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
            'funded' => ['label' => Craft::t('app', 'Funded')],
            'goal' => ['label' => Craft::t('app', 'Goal')],
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
            'goal' => Craft::t('app', 'Goal'),
            'funded' => Craft::t('app', 'Funded'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'subtitle', 'projectId'];
    }

    public function getCpEditUrl(): ?string
    {
        return 'fundraising/projects/edit/' . $this->id;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['title', 'projectId', 'goal', 'startDate', 'endDate'], 'required'];
        return $rules;
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
        $record->heroImage = $this->heroImage;
        $record->mediaGallery = $this->mediaGallery;
        $record->save(false);
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    public function getFieldLayout(): null
    {
        return null; // If you have a field layout, define here.
    }
}
