<?php

namespace PierreMiniggio\YoutubeToFacebookPage\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $facebookId,
        int $facebookAccountId,
        int $youtubeVideoId
    ): void
    {
        $this->connection->start();
        $postQueryParams = [
            'account_id' => $facebookAccountId,
            'facebook_id' => $facebookId
        ];
        $findPostIdQuery = ['
            SELECT id FROM facebook_post
            WHERE account_id = :account_id
            AND facebook_id = :facebook_id
            ;
        ', $postQueryParams];
        $queriedIds = $this->connection->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO facebook_post (account_id, facebook_id)
                VALUES (:account_id, :facebook_id)
                ;
            ', $postQueryParams);
            $queriedIds = $this->connection->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'facebook_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM facebook_post_youtube_video
            WHERE facebook_id = :facebook_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO facebook_post_youtube_video (facebook_id, youtube_id)
                VALUES (:facebook_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
