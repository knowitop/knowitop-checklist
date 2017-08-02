<?php

namespace Knowitop;

Class HtmlRenderer
{
    public static function render($tag, $attrs = null, $contents = null)
    {
        $tags = preg_split('/ \s*>\s* /x', $tag, 2);
        if (isset($tags[1])) {
            $contents = static::render($tags[1], null, $contents);
        }
        $classes = explode('.', $tags[0]);
        $tag = array_shift($classes);
        $classes = empty($classes) ? '' : 'class="' . implode(' ', $classes) . '"';
        $attrs = is_array($attrs) ? array_reduce(array_keys($attrs), function ($carry, $key) use ($attrs) { return "$carry $key=\"$attrs[$key]\""; }, '') : $attrs;
        $html = "<$tag $classes $attrs>";
        if (!self::isSelfClosingTag($tag))
        {
            $contents = is_array($contents) ? implode('', $contents) : $contents;
            $html .= "$contents</$tag>";
        }
        return $html;
    }

    protected static function isSelfClosingTag($tag)
    {
        return in_array(strtolower($tag), array(
            'area', 'base', 'br', 'col', 'embed', 'hr',
            'img', 'input', 'keygen', 'link', 'menuitem',
            'meta', 'param', 'source', 'track', 'wbr',
        ));
    }
}