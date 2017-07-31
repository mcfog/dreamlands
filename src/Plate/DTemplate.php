<?php namespace Dreamlands\Plate;

use Dreamlands\DContainer;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Entity\UserEntity;
use Hashids\Hashids;
use Identicon\Identicon;
use League\Plates\Engine;
use League\Plates\Template\Template;

class DTemplate extends Template
{
    /**
     * @var Hashids
     */
    private $hashids;
    /**
     * @var Identicon
     */
    private $identicon;
    /**
     * @var DContainer
     */
    private $container;

    public function __construct(Engine $engine, $name, Hashids $hashids, Identicon $identicon, DContainer $container)
    {
        parent::__construct($engine, $name);
        $this->hashids = $hashids;
        $this->identicon = $identicon;
        $this->container = $container;
    }

    public function userHashId(UserEntity $userEntity = null)
    {
        return $userEntity ? $this->hashids->encode($userEntity->id) : 'nil';
    }

    public function userAvatar(UserEntity $userEntity = null)
    {
        if (!$userEntity) {
            return '/assets/image/nobody.png';
        }

        return $this->identicon->getImageDataUri(sprintf('%d+%d', $userEntity->id, $userEntity->hash), 40);
    }


    public function postContent(PostEntity $postEntity)
    {
        switch ($postEntity->content_type) {
            case PostEntity::CONTENT_TYPE_HTML:
                return $postEntity->content;
            case PostEntity::CONTENT_TYPE_PLAIN:
                /**
                 * @var PlainContentFormatter $formatter
                 */
                $formatter = $this->container->instantiate(PlainContentFormatter::class);

                return $formatter->format($postEntity->content);
            default:
                return '???';
        }
    }

    public function postTitle(PostEntity $postEntity)
    {
        $class = empty($postEntity->title) ? ' muted' : '';
        $title = htmlspecialchars($postEntity->title ?: '无标题', ENT_QUOTES);
        return <<<HTML
<span class="{$class} title">{$title}</span>
HTML
            ;
    }
}
