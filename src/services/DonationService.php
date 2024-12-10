<?php
namespace madebythink\fundraising\services;

use craft\base\Component;
use madebythink\fundraising\records\DonationRecord;
use madebythink\fundraising\elements\Project;
use Craft;

class DonationsService extends Component
{
    public function recordDonation(array $donationData)
    {
        // Expected fields in donationData: projectId (string), amount (float),
        // donorName (string), comment (string), publicComment (bool)
        $project = Craft::$app->getPlugins()->getPlugin('craft-fundraising')->projects->getProjectByProjectId($donationData['projectId'] ?? '');
        if (!$project) {
            Craft::error("No project found for donation with projectId: " . ($donationData['projectId'] ?? ''), __METHOD__);
            return;
        }

        $donation = new DonationRecord();
        $donation->projectId = $project->id;
        $donation->donorName = $donationData['donorName'] ?? null;
        $donation->amount = (float)$donationData['amount'] ?? 0.0;
        $donation->comment = $donationData['comment'] ?? null;
        $donation->publicComment = (bool)($donationData['publicComment'] ?? false);
        $donation->save(false);

        // Update project funded amount
        Craft::$app->getPlugins()->getPlugin('craft-fundraising')->projects->updateFunding($project, $donation->amount);
    }

    public function getDonationsForProject(Project $project, bool $onlyPublic = false)
    {
        $query = DonationRecord::find()->where(['projectId' => $project->id]);
        if ($onlyPublic) {
            $query->andWhere(['publicComment' => true]);
        }

        return $query->all();
    }
}
