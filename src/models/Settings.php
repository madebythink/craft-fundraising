<?php

namespace madebythink\fundraising\models;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

class Settings extends Model
{
    public ?string $defaultCurrency = 'AUD';
    public ?bool $enablePublicCommentsByDefault = false;
    public ?string $defaultGoalCurrencySymbol = '$';

    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => [
                    'defaultCurrency',
                    'enablePublicCommentsByDefault',
                    'defaultGoalCurrencySymbol'
                ],
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['defaultCurrency'], 'required'],
            ['defaultCurrency', 'validateCurrency'],
            [['enablePublicCommentsByDefault', 'defaultGoalCurrencySymbol'], 'safe'],
        ];
    }

    // Validate that the Currency setting
    public function validateCurrency($attribute, $params, $validator)
    {
        $environment = App::parseEnv($this->$attribute);

        // Determine if the currency is a valid one
        if (!in_array($environment, ['AUD', 'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'CHF', 'CNY', 'HKD', 'SGD', 'NZD', 'SEK', 'DKK', 'NOK', 'MXN', 'BRL', 'ARS', 'CLP', 'COP', 'PEN'])) {
            $this->addError($attribute, 'Currency must be a valid currency code.');
        }

    }

    public function getDefaultCurrency(): ?string
    {
        return App::parseEnv($this->defaultCurrency);
    }
    
    public function getEnablePublicCommentsByDefault(): ?string
    {
        return App::parseEnv($this->enablePublicCommentsByDefault);
    }

    public function getDefaultGoalCurrencySymbol(): ?string
    {
        return App::parseEnv($this->defaultGoalCurrencySymbol);
    }
}
