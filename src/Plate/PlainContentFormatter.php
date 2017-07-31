<?php namespace Dreamlands\Plate;

class PlainContentFormatter
{
    protected $hasImage = false;

    public function format($content)
    {
        if (empty($content)) {
            return <<<'HTML'
<span class="muted">无内容</span>
HTML;
        }
        $lines = array_map([$this, 'formatLine'], explode("\n", $content));

        return implode('<br>', $lines);
    }

    protected function formatLine($line)
    {
        switch (true) {
            case preg_match('/^>> (\d+)$/', trim($line), $matches):
                return <<<HTML
<span class="post-quote" data-post="{$matches[1]}">{$matches[1]}</span>
HTML;

            case !$this->hasImage && preg_match('#^!(https?:[!-~]+)$#', trim($line), $matches):
                $this->hasImage = true;
                return <<<HTML
<img class="external" src="{$matches[1]}" alt="">
HTML;

            default:
                return self::linkify(htmlspecialchars($line));
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
}
