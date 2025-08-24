<?php

namespace Knowitop\iTop\Extension\Checklist;

use MetaModel;
use utils;

class ModuleConfig
{
	private const MODULE_NAME = 'knowitop-checklist';

	/**
	 * @param string $sName
	 * @param mixed|null $sDefaultValue
	 *
	 * @return mixed|null
	 */
	public static function Get(string $sName, $sDefaultValue = null)
	{
		return MetaModel::GetModuleSetting(self::GetName(), $sName, $sDefaultValue);
	}

	/**
	 * @return string
	 */
	public static function GetName(): string
	{
		return self::MODULE_NAME;
	}

	public static function GetPath(): string
	{
		return utils::GetAbsoluteModulePath(static::GetName());
	}

	public static function GetRootUrl(): string
	{
		return utils::GetAbsoluteUrlModulesRoot().'/'.static::GetName().'/';
	}

	public static function GetAssetsUrl(): string
	{
		return static::GetRootUrl().'assets/';
	}

	public static function GetImgAssetsUrl()
	{
		return static::GetRootUrl().'assets/img/';
	}

	public static function GetJsAssetsUrl()
	{
		return static::GetRootUrl().'assets/js/';
	}

	public static function GetCssAssetsUrl()
	{
		return static::GetRootUrl().'assets/css/';
	}
}