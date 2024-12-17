<?php
namespace madebythink\fundraising\fields;

use craft\base\Field;
use craft\helpers\Html;
use madebythink\fundraising\elements\Project;

class ProjectField extends Field
{
    public static function displayName(): string
    {
        return 'Project Selector';
    }

    public function getInputHtml($value, $element = null): string
    {
        $projects = Project::find()->all();
        $options = [];
        foreach ($projects as $project) {
            $options[$project->id] = $project->title;
        }

        return Html::dropDownList($this->handle, $value, $options, ['class' => 'form-control']);
    }
}
