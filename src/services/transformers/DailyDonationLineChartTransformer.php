<?php
namespace madebythink\fundraising\services\transformers;

class DailyDonationLineChartTransformer
{
    public function transform(array $donations): array
    {
        $chartData = ['labels' => [], 'data' => []];
        foreach ($donations as $donation) {
            $chartData['labels'][] = $donation['date'];
            $chartData['data'][] = $donation['amount'];
        }
        return $chartData;
    }
}