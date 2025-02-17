<?php
/***************************************************************
* Copyright notice
*
*   2010 Daniel Lienert <daniel@lienert.cc>, Michael Knoll <mimi@kaktusteam.de>
* All rights reserved
*
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
* 
* @package ViewHelpers
* @subpackage Widget\Controller
* @author Daniel Lienert <daniel@lienert.cc>
*/

class Tx_Yag_ViewHelpers_Widget_Controller_ThemeSelectorController extends Tx_Yag_ViewHelpers_Widget_Controller_AbstractWidgetController {

	/**
	 * @var t3lib_Registry
	 */
	protected $registry;


	/**
	 * @return void
	 */
	public function initializeAction() {
		parent::initializeAction();

		$this->registry = t3lib_div::makeInstance('t3lib_Registry'); /** @var $registry t3lib_Registry */
	}


	/**
	 * @return void
	 */
	public function indexAction() {
		$selectedThemes = $this->registry->get('tx_yag', 'rfcSelectedThemes', serialize(array()));
		$selectedThemesArray = unserialize($selectedThemes);

		$themes = array();

		$themeCollection = $this->configurationBuilder->buildThemeConfigurationCollection();
		foreach($themeCollection as $theme) { /** @var $theme Tx_Yag_Domain_Configuration_Theme_ThemeConfiguration */
			if($theme->getName() != 'backend') {
			  $themes[$theme->getName()] = array(
					'title' => $theme->getTitle(),
					'selected' => in_array($theme->getName(), $selectedThemesArray) ? $selectedThemesArray[$theme->getName()] : false,
				);
			}
		}


		$this->view->assign('themes', $themes);
	}


	/**
	 * @return void
	 */
	public function selectThemeAction() {

		$selectedThemes = t3lib_div::_GET('selectedThemes');

		foreach($selectedThemes as $theme => $isSelected) {
			$themeName = end(explode('.', $theme));
			$selectedThemeNames[$themeName] = $isSelected == 'true' ? true : false;
		}


		$this->registry->set('tx_yag', 'rfcSelectedThemes', serialize($selectedThemeNames));

		exit();
	}
}
?>