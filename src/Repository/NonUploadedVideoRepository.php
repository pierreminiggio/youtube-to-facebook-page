<?php

namespace PierreMiniggio\YoutubeToFacebookPage\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByFacebookAndYoutubeChannelIds(int $facebookAccountId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $postedFacebookPostIds = $this->connection->query('
            SELECT f.id
            FROM facebook_post as f
            RIGHT JOIN facebook_post_youtube_video as fpyv
            ON f.id = fpyv.facebook_id
            WHERE f.account_id = :account_id
        ', ['account_id' => $facebookAccountId]);
        $postedFacebookPostIds = array_map(fn ($entry) => (int) $entry['id'], $postedFacebookPostIds);

        $postsToPost = $this->connection->query('
            SELECT
                y.id,
                y.title,
                y.url
            FROM youtube_video as y
            ' . (
                $postedFacebookPostIds
                    ? 'LEFT JOIN facebook_post_youtube_video as fpyv
                    ON y.id = fpyv.youtube_id
                    AND fpyv.facebook_id IN (' . implode(', ', $postedFacebookPostIds) . ')'
                    : ''
            ) . '
            
            WHERE y.channel_id = :channel_id
            ' . ($postedFacebookPostIds ? 'AND fpyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $postsToPost;
    }
}
