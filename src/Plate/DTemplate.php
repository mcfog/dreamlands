<?php namespace Dreamlands\Plate;

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

    public function __construct(Engine $engine, $name, Hashids $hashids, Identicon $identicon)
    {
        parent::__construct($engine, $name);
        $this->hashids = $hashids;
        $this->identicon = $identicon;
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
                if (!empty($postEntity->content)) {
                    $lines = array_map(function ($line) {
                        switch (true) {
                            case preg_match('/^>> (\d+)$/', trim($line), $matches):

                                $line = htmlspecialchars($line);
                                return <<<HTML
<span class="post-quote" data-post="{$matches[1]}">{$matches[1]}</span>
HTML;
                            default:
                                return self::linkify(htmlspecialchars($line));
                        }
                    }, explode("\n", $postEntity->content));

                    return implode('<br>', $lines);
                }
                return <<<'HTML'
<span class="muted">无内容</span>
HTML;
            default:
                return '???';
        }
    }

    protected static function linkify($html)
    {
        //@ref http://www.catonmat.net/blog/my-favorite-regex/  [!-~]匹配除了空格以外的全部可见ascii字符
        return preg_replace(<<<'REGEX'
#((https?|magnet):[!-~]+)#
REGEX
            , <<<'HTML'
 <a href="$1" class="external" rel="nofollow noopener noreferer" target="_blank">$1</a> 
HTML
            , $html);
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
