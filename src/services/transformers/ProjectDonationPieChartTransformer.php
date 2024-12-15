<?php
namespace madebythink\fundraising\services\transformers;

use madebythink\fundraising\Fundraising;

class ProjectDonationPieChartTransformer
{
    public function transform(array $projects): array
    {
        $chartData = ['labels' => [], 'data' => [], 'backgroundColors' => [], 'borderColors' => []];
        $backgroundColors = ['rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'];
        $borderColors = ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'];

        // Get the setting for the currency symbol
        $settings = Fundraising::getInstance()->getSettings();
        $currencySymbol = $settings->getDefaultGoalCurrencySymbol();


        foreach ($projects as $index => $project) {
            $chartData['labels'][] = $project->title;
            $chartData['data'][] = $project->funded;
            $chartData['backgroundColors'][] = $backgroundColors[$index % count($backgroundColors)]; // Cycle colors
            $chartData['borderColors'][] = $borderColors[$index % count($borderColors)];
        }

        return $chartData;
    }
}