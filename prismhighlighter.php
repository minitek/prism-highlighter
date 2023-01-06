<?php

/**
 * @title        Prism Highlighter
 * @copyright    Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Plugin class for System - Prism Highlighter.
 */
class plgSystemPrismHighlighter extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->document = Factory::getDocument();
	}

	/**
	 * This event is triggered immediately before the framework has rendered the application.
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		if ($this->document->getType() != 'html')
			return;

		$view = $this->app->input->get('view');
		$layout = $this->app->input->get('layout');

		if ($this->app->isClient('site')) 
		{
			if ($view == 'form' && $layout == 'edit')
				return;
			
			$this->loadFrontendAssets();
		}
	}

	/**
	 * Method to load the front-end assets.
	 */
	function loadFrontendAssets()
	{
		$wa = $this->document->getWebAssetManager();

		$attribs = array('defer' => $defer = true, 'data-manual' => false);
		$local = 'plg_system_prismhighlighter/';
		$cdnjs = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/';
		$jsdelivr = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/';
		$source = $this->params->get('source', 'local');

		if ($source == 'cdnjs')
			$cdn = $cdnjs;
		elseif ($source == 'jsdelivr')
			$cdn = $jsdelivr;

		switch ($source)
		{
			case 'cdnjs':
			case 'jsdelivr':
				$theme = $cdn.'themes/'.$this->params->get('theme', 'prism.min.css');
				$core = $cdn.'prism.min.js';
				$autoloader = $cdn.'plugins/autoloader/prism-autoloader.min.js';
				$unescaped = $cdn.'plugins/unescaped-markup/prism-unescaped-markup.min.js';
				$normalize = $cdn.'plugins/normalize-whitespace/prism-normalize-whitespace.min.js';
				$linenumbers_css = $cdn.'plugins/line-numbers/prism-line-numbers.min.css';
				$linenumbers_js = $cdn.'plugins/line-numbers/prism-line-numbers.min.js';
				$toolbar_css = $cdn.'plugins/toolbar/prism-toolbar.min.css';
				$toolbar_js = $cdn.'plugins/toolbar/prism-toolbar.min.js';
				$copy = $cdn.'plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js';
				$download = $cdn.'plugins/download-button/prism-download-button.min.js';
				$language = $cdn.'plugins/show-language/prism-show-language.min.js';
				break;
			case 'local':
			default:
				$theme = $local.$this->params->get('theme', 'prism.min.css');
				$core = $local.'prism.min.js';
				$autoloader = $local.'prism-autoloader.min.js';
				$unescaped = $local.'prism-unescaped-markup.min.js';
				$normalize = $local.'prism-normalize-whitespace.min.js';
				$linenumbers_css = $local.'prism-line-numbers.min.css';
				$linenumbers_js = $local.'prism-line-numbers.min.js';
				$toolbar_css = $local.'prism-toolbar.min.css';
				$toolbar_js = $local.'prism-toolbar.min.js';
				$copy = $local.'prism-copy-to-clipboard.min.js';
				$download = $local.'prism-download-button.min.js';
				$language = $local.'prism-show-language.js';
				$file = $local.'prism-file-highlight.js';
				break;
		}

		// Theme
		$wa->registerAndUseStyle('plg_system_prismhighlighter.prism', $theme);

		// Core 
		$wa->registerAndUseScript('plg_system_prismhighlighter.prism', $core, [], $attribs);

		// Autoloader
		$wa->registerAndUseScript('plg_system_prismhighlighter.prism-autoloader', $autoloader, [], ['defer' => $defer]);

		// Unescaped markup
		if ($this->params->get('unescaped_markup', true))
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-unescaped-markup', $unescaped, [], ['defer' => $defer]);

		// Normalize whitespace
		if ($this->params->get('normalize_whitespace', true))
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-normalize-whitespace', $normalize, [], ['defer' => $defer]);

		// Line numbers
		if ($this->params->get('line_numbers', false))
		{
			$wa->registerAndUseStyle('plg_system_prismhighlighter.prism-line-numbers', $linenumbers_css);
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-line-numbers', $linenumbers_js, [], ['defer' => $defer]);
		}
		
		$show_language = $this->params->get('show_language', false);
		$copy_to_clipboard = $this->params->get('copy_to_clipboard', false);
		$download_button = $this->params->get('download_button', false);
		$toolbar = $show_language || $copy_to_clipboard || $download_button;

		// Toolbar
		if ($toolbar)
		{
			$wa->registerAndUseStyle('plg_system_prismhighlighter.prism-toolbar', $toolbar_css);
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-toolbar', $toolbar_js, [], ['defer' => $defer]);
		}

		// Show language
		if ($show_language)
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-show-language', $language, [], ['defer' => $defer]);

		// Copy to clipboard
		if ($copy_to_clipboard)
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-copy-to-clipboard', $copy, [], ['defer' => $defer]);

		// Download button
		if ($download_button)
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-download-button', $download, [], ['defer' => $defer]);

		// File highlight
		if ($source == 'local' && $this->params->get('file_highlight', false))
			$wa->registerAndUseScript('plg_system_prismhighlighter.prism-file-highlight', $file, [], ['defer' => $defer]);
	}
}
