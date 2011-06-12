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
 * Class implements Category Tree Builder domain object
 *
 * @package Domain
 * @subpackage Model
 * @author Michael Knoll <mimi@kaktusteam.de>
 * @author Daniel Lienert <daniel@lienert.cc>
 * @author Joachim Mathes <joachim_mathes@web.de>
 */
class Tx_Yag_Domain_Model_CategoryTreeBuilder {

	/**
	 * Holds an instance of category repository
	 *
	 * @var Tx_Yag_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;
	
	
	
	/**
	 * Constructor for treebuilder. Requires category repository as parameter.
	 *
	 * @param Tx_Yag_Domain_Repository_CategoryRepository $categoryRepository
	 */
	public function __construct(Tx_Yag_Domain_Repository_CategoryRepository $categoryRepository) {
		$this->categoryRepository = $categoryRepository;
	}
	
	
	
	/**
	 * Builds a tree for a given category. The tree is build up from the root of given category
	 *
	 * @param Tx_Yag_Domain_Model_Category $category
	 * @return Tx_Yag_Domain_Model_CategoryTree
	 */
	public function buildTreeForCategory(Tx_Yag_Domain_Model_Category $category) {
		$nodes = $this->categoryRepository->findByRootId($category->getRoot())->toArray();
		$stack = new Tx_Yag_Domain_Model_Stack();
		foreach($nodes as $node) { 
			#echo "<br><br>Knoten: " . $node->toString();
			if ($stack->isEmpty() || $stack->top()->getRgt() > $node->getRgt()) {
				$stack->push($node);
				#echo "Pushed on stack:" . $stack->toString();
			} else {
				#echo "Adding children:";
				while(!$stack->isEmpty() && $stack->top()->getRgt() < $node->getRgt()) {
					#echo "In while - current node " . $node->toString() . " current topStack: " . $stack->top()->toString();
					$stack->top()->setParent($node, false);
					$node->addChild($stack->top(),false);
					$stack->pop();
					#echo "After while-iteration: ". $stack->toString();
				}
				$stack->push($node);
				#echo "After pushing after while: <ul>" . $stack->toString() . "</ul>";
			}
		}
		$tree = new Tx_Yag_Domain_Model_CategoryTree($stack->top());
		#echo "Finished tree: " . $tree->toString();
		return $tree;
	}
	
}

?>