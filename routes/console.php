<?php

use App\Models\SocialConnection;
use App\Models\SocialPost;
use App\Services\Social\LinkedInConnector;
use App\Services\Social\TwitterConnector;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('social:publish-scheduled', function () {
    // 1. Find posts to publish
    $posts = SocialPost::where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->get();

    if ($posts->isEmpty()) {
        $this->info('Aucun post à publier.');

        return;
    }

    $totalPublished = 0;
    $totalFailed = 0;

    // 2. Loop through each post
    foreach ($posts as $post) {
        $this->info("Publication du post #{$post->id}...");

        // 3. Retrieve active connections
        $connections = SocialConnection::whereIn('id', $post->connection_ids ?? [])
            ->where('is_active', true)
            ->get();

        if ($connections->isEmpty()) {
            $post->markAsFailed('Aucune connexion active');
            $totalFailed++;

            continue;
        }

        $published = 0;
        $errors = [];

        // 4. Publish to each connection
        foreach ($connections as $connection) {
            try {
                $result = null;

                // Switch on platform
                switch ($connection->platform) {
                    case 'twitter':
                        $connector = new TwitterConnector($connection);
                        $result = $connector->publishTweet($post);
                        break;

                    case 'linkedin':
                        $connector = new LinkedInConnector($connection);
                        $result = $connector->publishPost($post);
                        break;

                    default:
                        $errors[] = "Plateforme {$connection->platform} non supportée";
                        continue 2;
                }

                if ($result['success']) {
                    $published++;
                    $this->line("  ✓ Publié sur {$connection->platform}");
                } else {
                    $errors[] = "{$connection->name}: {$result['error']}";
                    $this->error("  ✗ Erreur sur {$connection->platform}");
                }
            } catch (\Exception $e) {
                $errors[] = "{$connection->name}: {$e->getMessage()}";
                $this->error("  ✗ Exception sur {$connection->platform}");
            }
        }

        // 5. Update post status
        if ($published > 0) {
            $post->markAsPublished();
            $totalPublished++;
            $this->info("  ✓ Post #{$post->id} publié avec succès");
        } else {
            $post->markAsFailed(implode('; ', $errors));
            $totalFailed++;
            $this->error("  ✗ Post #{$post->id} échoué");
        }
    }

    // 6. Final report
    $this->newLine();
    $this->info('Publication terminée:');
    $this->line("  • Posts publiés: {$totalPublished}");
    $this->line("  • Posts échoués: {$totalFailed}");

})->purpose('Publier automatiquement les posts sociaux planifiés');

// Scheduler configuration
Schedule::command('social:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
