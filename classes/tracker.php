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
SELECT DISTINCT ON (post.id) topic.title, post.date, forum.name
FROM post
  LEFT JOIN topic ON post.idtopic = topic.id
  LEFT JOIN forum ON topic.idforum = forum.id
ORDER BY post.id DESC
LIMIT 10;
SQL;

        $result = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllPost($page = 0)
    {

    }
}








