<?php namespace Dreamlands\Plate;

use Dreamlands\Entity\PostEntity;
use Dreamlands\Entity\UserEntity;
use Identicon\Identicon;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlateExtension implements ExtensionInterface
{
    /**
     * @var Identicon
     */
    private $identicon;

    public function __construct(Identicon $identicon)
    {
        $this->identicon = $identicon;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('postTitle', [$this, 'postTitle']);
        $engine->registerFunction('postContent', [$this, 'postContent']);
        $engine->registerFunction('userAvatar', [$this, 'userAvatar']);
    }

    public function userAvatar(UserEntity $userEntity = null)
    {
        if (!$userEntity) {
            return '/assets/image/nobody.png';
        }

        return $this->identicon->getImageDataUri($userEntity->id . sha1($userEntity->name), 48);
    }

    public function postContent(PostEntity $postEntity)
    {
        switch ($postEntity->contentType) {
            case PostEntity::CONTENT_TYPE_HTML:
                return $postEntity->content;
            case PostEntity::CONTENT_TYPE_PLAIN:
                return nl2br(htmlspecialchars($postEntity->content));
            default:
                return '???';
        }
    }

    public function postTitle(PostEntity $postEntity)
    {
        $class = empty($postEntity->title) ? ' muted' : '';
        $title = $postEntity->title ?: '无标题';
        return <<<HTML
<span class="{$class} title">{$title}</span>
HTML
            ;
    }
}
