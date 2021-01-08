<?php

namespace PierreMiniggio\YoutubeToFacebookPage\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class LinkedChannelRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findAll(): array
    {
        $this->connection->start();
        $channels = $this->connection->query('
            SELECT
                fpyc.youtube_id as y_id,
                f.id as f_id,
                f.facebook_id,
                f.api_url,
                f.api_token
            FROM facebook_page as f
            RIGHT JOIN facebook_page_youtube_channel as fpyc
                ON f.id = fpyc.facebook_id
        ', []);
        $this->connection->stop();

        return $channels;
    }
}
