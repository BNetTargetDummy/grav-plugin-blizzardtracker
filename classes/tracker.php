<?php

namespace Grav\Plugin\BlizzardTracker;


use Grav\Common\GravTrait;

class Tracker
{
    use GravTrait;

    /** @var  \PDO */
    private $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO(
            self::getGrav()['config']->get('plugins.blizzardtracker.pdo.driver') . ':host=' .
            self::getGrav()['config']->get('plugins.blizzardtracker.pdo.host') . ';dbname=' .
            self::getGrav()['config']->get('plugins.blizzardtracker.pdo.database') . ';',
            self::getGrav()['config']->get('plugins.blizzardtracker.pdo.user'),
            self::getGrav()['config']->get('plugins.blizzardtracker.pdo.password')
        );
    }

    public function getLastPosts($limit = null): array
    {
        if (is_null($limit)) {
            $limit = self::getGrav()['config']->get('plugins.blizzardtracker.config.limitMainPage');
        }
        $limit = (int)$limit;

        $sql = <<<SQL
SELECT DISTINCT ON (post.id)
  forum.name       AS forum_name,
  topic.title      AS topic_title,
  topic.authorbnet AS topic_author,
  topic.dateposted AS topic_date,
  post.author      AS last_post_author,
  post.date        AS last_post_date
FROM post
  LEFT JOIN topic ON post.idtopic = topic.id
  LEFT JOIN forum ON topic.idforum = forum.id
ORDER BY post.id DESC
LIMIT :limit;
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllTopic(int $page = 0, $count = null)
    {
        if (is_null($count)) {
            $count = self::getGrav()['config']->get('plugins.blizzardtracker.config.postPerPage');
        }
        $count = (int)$count;

        $sql = <<<SQL
SELECT DISTINCT ON (post.id)
  forum.name       AS forum_name,
  topic.title      AS topic_title,
  topic.authorbnet AS topic_author,
  post.author      AS last_post_author,
  post.date        AS last_post_date
FROM post
  LEFT JOIN topic ON post.idtopic = topic.id
  LEFT JOIN forum ON topic.idforum = forum.id
ORDER BY post.id DESC
LIMIT :limit OFFSET :offset;
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $count);
        $stmt->bindValue(':offset', $count * $page);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getTopic(int $idTopic): array
    {
        $sql = <<<SQL
SELECT
  forum.name       AS forum_name,
  topic.title      AS title,
  topic.authorbnet AS author,
  topic.dateposted AS dateposted,
  topic.replies    AS number_replies
FROM topic
  LEFT JOIN forum ON topic.idforum = forum.id
WHERE topic.id = :idtopic;
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idtopic' => $idTopic]);
        $topic = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($topic)) {
            throw new \Exception('Topic not found');
        }

        $sql = <<<SQL
SELECT
  post.author     AS author,
  post.date       AS dateposted,
  post.postnumber AS postnumber,
  post.content    AS content
FROM post
WHERE post.idtopic = :idtopic
ORDER BY post.id ASC;
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idtopic' => $idTopic]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if(empty($posts)) {
            $posts = [];
        }

        $topic['posts'] = $posts;

        return $topic;
    }
}