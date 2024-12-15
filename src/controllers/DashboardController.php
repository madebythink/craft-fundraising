<?php
namespace madebythink\fundraising\controllers;

use Craft;
use craft\web\Controller;
use madebythink\fundraising\Fundraising;
use madebythink\fundraising\elements\Project;
use madebythink\fundraising\services\transformers\ProjectDonationPieChartTransformer;
use madebythink\fundraising\services\transformers\DailyDonationLineChartTransformer;


class DashboardController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex()
    {
        // Fetch some overview data
        $projects = Project::find()->all();

        // Example aggregated data:
        $totalRaised = 0;
        $activeProjects = [];
        $projectsPie = null;
        $now = new \DateTime();

        foreach ($projects as $project) {
            if ($project->endDate > $now && $project->startDate <= $now) {
                $activeProjects[] = $project;
            }
            $totalRaised += $project->funded;
        }

        // Transform data for pie chart
        $pieChartTransformer = new ProjectDonationPieChartTransformer();
        $pieChartData = $pieChartTransformer->transform($projects);
        $pieChartData['label'] = 'Project Contributions';

        // Get Donation data (currently placeholder)
        $donations = [
            ['date' => '2024-12-01', 'amount' => 50],
            ['date' => '2024-12-02', 'amount' => 100],
            ['date' => '2024-12-03', 'amount' => 150],
            ['date' => '2024-12-04', 'amount' => 280],
            ['date' => '2024-12-05', 'amount' => 130],
            ['date' => '2024-12-06', 'amount' => 300],
            ['date' => '2024-12-07', 'amount' => 20],
        ];

        $lineChartTransformer = new DailyDonationLineChartTransformer();
        $lineChartData = $lineChartTransformer->transform($donations);
        $lineChartData['label'] = 'Daily Donations';
        $lineChartData['fill'] = true;
        $lineChartData['borderColor'] = 'rgba(75, 192, 192, 1)';
        $lineChartData['backgroundColor'] = 'rgba(75, 192, 192, 0.2)';

        return $this->renderTemplate('fundraising/dashboard/index', [
            'activeProjects' => $activeProjects,
            'totalRaised' => $totalRaised,
            'projectsCount' => count($projects),
            'pieChartData' => $pieChartData,
            'lineChartData' => $lineChartData,
            'pluginHandle' => Fundraising::$pluginHandle,
        ]);
    }
}
