<?php

namespace Knowitop\iTop\Extension\Checklist\Helper;

Class SimpleHtmlRenderer
{
	/**
	 * @param string $tag
	 * @param null $attrs
	 * @param null $contents
	 *
	 * @return string
	 */
	public static function render(string $tag, $attrs = null, $contents = null): string
	{
		$tags = preg_split('/ \s*>\s* /x', $tag, 2);
		if (isset($tags[1]))
		{
			$contents = static::render($tags[1], null, $contents);
		}
		$classes = explode('.', $tags[0]);
		$tag = array_shift($classes);
		$classes = !empty($classes) ? 'class="'.implode(' ', $classes).'"' : '';
		if (is_array($attrs))
		{
			$attrs = array_reduce(array_keys($attrs),
				function ($carry, $key) use ($attrs) {
					return is_numeric($key) ? "$carry $attrs[$key]" : "$carry $key=\"$attrs[$key]\"";
				}, '');
		}
		$html = "<$tag $classes $attrs>";
		if (!self::isSelfClosingTag($tag))
		{
			$contents = is_array($contents) ? implode('', $contents) : $contents;
			$html .= "$contents</$tag>";
		}

		return $html;
	}

	protected static function isSelfClosingTag(string $tag): bool
	{
		return in_array(strtolower($tag), array(
			'area',
			'base',
			'br',
			'col',
			'embed',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'menuitem',
			'meta',
			'param',
			'source',
			'track',
			'wbr',
		));
	}
}