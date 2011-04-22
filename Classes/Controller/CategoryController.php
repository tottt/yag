<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Michael Knoll <mimi@kaktusteam.de>
*  			Daniel Lienert <daniel@lienert.cc>
*  			
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Controller for the Category object
 *
 * @package Controller
 * @author Michael Knoll <mimi@kaktusteam.de>
 * @author Daniel Lienert <daniel@lienert.cc>
 */
class Tx_Yag_Controller_CategoryController extends Tx_Yag_Controller_AbstractController {
	
	/**
	 * @var Tx_Yag_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;

	
	
	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	protected function postInitializeAction() {
		$this->categoryRepository = t3lib_div::makeInstance('Tx_Yag_Domain_Repository_CategoryRepository');
	}
	
	
	/**
	 * TODO test method
	 */
	public function showAction() {
		/*
		$rootCategory = $this->categoryRepository->findByUid(1);
		echo $rootCategory->getName() . '<br>';
		foreach($rootCategory->getChildren() as $child) {
			echo "-" . $child->getName() . '<br>';
			foreach($child->getChildren() as $subchild) {
				echo "--" . $subchild->getName() . '<br>';
			}
		}
		die();
		*/
		$pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
  	$pageRenderer->addExtDirectCode();
  	
  	// calling of our own method on the client-side
//  	$pageRenderer->addExtOnReadyCode('
//  		TYPO3.YAG.ExtDirect.getSubTree("1", function(result, options) {
//  			if (typeof console == "object") {
//  				console.log(result);
//  			} else {
//  				alert(result);
//  			}
//  		}, this);
//  	');
		
	}
	
	
	public function getSubTreeAction() {
		
		$node = t3lib_div::_GP('node');
		if($node=='root') $node = 1;
		
		$category = $this->categoryRepository->findByUid($node);
		
		$childCategoryArray = array();
		
		foreach($category->getChildren() as $childCategory) { /* @var $childCategory Tx_Yag_Domain_Model_Category */
			$childCategoryArray[] = array(
				'id' => $childCategory->getUid(),
				'text' => $childCategory->getName(),
				'leaf' => $childCategory->hasChildren() ? '': 'true'
			);

		}
		
		return json_encode($childCategoryArray);
	}

}
?>