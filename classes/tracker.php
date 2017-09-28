<?php
namespace Grav\Plugin\BlizzardTracker;


use Grav\Common\GravTrait;

//TODO: Fix BlizzardTracker so that the date is a DateTime in SQL

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

    public function getLastPost(): array
    {
        // TODO: Add plugin parameter to limit
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
LIMIT 10;
SQL;

        $result = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllTopic(int $page = 0, $count = null)
    {
        if(is_null($count)) {
            $count = self::getGrav()['config']->get('plugins.blizzardtracker.config.postPerPage');
        }
        $count = (int) $count;

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
        /**
         * Return array containing:
         *  - Main information about the topic
         *  - List of posts
         */
        return [];
    }
}