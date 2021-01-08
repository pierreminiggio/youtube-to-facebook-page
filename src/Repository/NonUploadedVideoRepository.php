<?php

namespace PierreMiniggio\YoutubeToFacebookPage\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByTwitterAndYoutubeChannelIds(int $twitterAccountId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $postedTwitterPostIds = $this->connection->query('
            SELECT f.id
            FROM facebook_post as f
            RIGHT JOIN facebook_post_youtube_video as fpyv
            ON f.id = fpyv.facebook_id
            WHERE f.account_id = :account_id
        ', ['account_id' => $twitterAccountId]);
        $postedTwitterPostIds = array_map(fn ($entry) => (int) $entry['id'], $postedTwitterPostIds);

        $postsToPost = $this->connection->query('
            SELECT
                y.id,
                y.title,
                y.url
            FROM youtube_video as y
            ' . (
                $postedTwitterPostIds
                    ? 'LEFT JOIN facebook_post_youtube_video as fpyv
                    ON y.id = fpyv.youtube_id
                    AND fpyv.facebook_id IN (' . implode(', ', $postedTwitterPostIds) . ')'
                    : ''
            ) . '
            
            WHERE y.channel_id = :channel_id
            ' . ($postedTwitterPostIds ? 'AND fpyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $postsToPost;
    }
}
