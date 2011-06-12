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
 * Class implements Category Tree domain object
 *
 * @package Domain
 * @subpackage Model
 * @author Michael Knoll <mimi@kaktusteam.de>
 * @author Daniel Lienert <daniel@lienert.cc>
 */
class Tx_Yag_Domain_Model_CategoryTree implements Tx_Yag_Domain_Model_TraversableInterface {

	/**
	 * Holds reference of root node for this tree
	 *
	 * @var Tx_Yag_Domain_Model_Category
	 */
	protected $rootNode = null;
	
	
	
	/**
	 * Holds a hashmap of nodes stored inside this tree
	 *
	 * @var array
	 */
	protected $treeMap = array();
	
	
	
	/**
	 * Holds a reference to a treewalker that updates nested set orderings
	 *
	 * @var Tx_Yag_Domain_Model_TreeWalker
	 */
	protected $nsTreeWalker;
	
	
	
	/**
	 * Factory method for instantiating a tree for a given root node
	 *
	 * @param Tx_Yag_Domain_Model_Category $rootNode
	 * @return Tx_Yag_Domain_Model_CategoryTree
	 */
	public static function getInstanceByRootNode(Tx_Yag_Domain_Model_Category $rootNode) {
		$tree = new Tx_Yag_Domain_Model_CategoryTree($rootNode);
		$treeWalker = new Tx_Yag_Domain_Model_TreeWalker(array(new Tx_Yag_Domain_Model_NestedSetVisitor()));
		$tree->injectNsUpdateTreeWalker($treeWalker);
		$tree->updateCategoryTree();
		return $tree;
	}
	
	
	
	/**
	 * Constructor for Category Tree
	 *
	 * @param Tx_Yag_Domain_Model_Category $rootNode Root node for category tree
	 */
	public function __construct(Tx_Yag_Domain_Model_Category $rootNode = null){
		$this->rootNode = $rootNode;
		$this->initTreeMap();
	}
	
	
	
	/**
	 * Injects a treewalker that updates nested set orderings
	 *
	 * @param Tx_Yag_Domain_Model_TreeWalker $treeWalker
	 */
	public function injectNsUpdateTreeWalker(Tx_Yag_Domain_Model_TreeWalker $treeWalker) {
		$this->nsTreeWalker = $treeWalker;
	}
	
	
	
	/**
	 * Returns root node of this category tree
	 *
	 * @return Tx_Yag_Domain_Model_Category
	 */
	public function getRoot() {
		return $this->rootNode;
	}
	
	
	
	/**
	 * Returns node for a given uid
	 *
	 * @param int $uid Uid of node
	 * @return Tx_Yag_Domain_Model_Category
	 */
	public function getNodeByUid($uid) {
		if (array_key_exists($uid, $this->treeMap)) {
			return $this->treeMap[$uid];
		} else {
			return null;
		}
	}
	
	
	
	/**
	 * Removes a node from the tree
	 *
	 * @param Tx_Yag_Domain_Model_Category $node
	 */
	public function deleteNode(Tx_Yag_Domain_Model_Category $node) {
		$subNodes = $node->getSubCategories();
		foreach($subNodes as $subnode) {
			$this->removeNodeFromTreeMap($subnode);
		}
		$this->removeNodeFromTreeMap($node);
		
		$node->getParent()->removeChild($node);
		
		$this->updateCategoryTree();
	}
	
	
	
	/**
	 * Moves a node given as first parameter into a node given as second parameter
	 *
	 * @param Tx_Yag_Domain_Model_Category $nodeToBeMoved Node to be moved
	 * @param Tx_Yag_Domain_Model_Category $targetNode Node to move moved node into
	 */
	public function moveNode(Tx_Yag_Domain_Model_Category $nodeToBeMoved, Tx_Yag_Domain_Model_Category $targetNode) {
		$this->checkForNodeBeingInTree($targetNode);
		$this->checkForNodeBeingInTree($nodeToBeMoved);
		
		// We remove moved node from children of its parent node
		if ($nodeToBeMoved->hasParent()) {
		    $nodeToBeMoved->getParent()->getChildren()->detach($nodeToBeMoved);
		}
		
		// We add moved node to children of target node
		$targetNode->addChild($nodeToBeMoved);
		
		// We set parent of moved node to target node
		$nodeToBeMoved->setParent($targetNode);
		
		$this->updateCategoryTree();
	}
	
	
	
	/**
	 * Moves a node given as a first parameter in front of a node given as a second parameter 
	 *
	 * @param Tx_Yag_Domain_Model_Category $nodeToBeMoved
	 * @param Tx_Yag_Domain_Model_Category $nodeToMoveBefore
	 */
	public function moveNodeBeforeNode(Tx_Yag_Domain_Model_Category $nodeToBeMoved, Tx_Yag_Domain_Model_Category $nodeToMoveBefore) {
		$this->checkForNodeBeingInTree($nodeToBeMoved);
		$this->checkForNodeBeingInTree($nodeToMoveBefore);
		
		// We remove node from children of its parent node
		if ($nodeToBeMoved->hasParent()) {
			$nodeToBeMoved->getParent()->getChildren()->detach($nodeToBeMoved);
		}
		
		// We add node to children of parent node of target node
		if ($nodeToMoveBefore->hasParent()) {
		   $parentOfTargetNode = $nodeToMoveBefore->getParent();
		   $parentOfTargetNode->addChildBefore($nodeToBeMoved, $nodeToMoveBefore);
		   $nodeToBeMoved->setParent($parentOfTargetNode);
		} else {
			throw new Exception("Trying to move a node in front of a node that doesn't have a parent node! 1307646534");
		}
		$this->updateCategoryTree();
	}
	
	
	
	/**
	 * Moves a node given as first parameter after a node given as second parameter
	 *
	 * @param Tx_Yag_Domain_Model_Category $nodeToBeMoved
	 * @param Tx_Yag_Domain_Model_Category $nodeToMoveAfter
	 */
	public function moveNodeAfterNode(Tx_Yag_Domain_Model_Category $nodeToBeMoved, Tx_Yag_Domain_Model_Category $nodeToMoveAfter) {
	    $this->checkForNodeBeingInTree($nodeToBeMoved);
        $this->checkForNodeBeingInTree($nodeToMoveAfter);
        
        // We remove node from children of its parent node
        if ($nodeToBeMoved->hasParent()) {
            $nodeToBeMoved->getParent()->getChildren()->detach($nodeToBeMoved);
        }
        
        // We add node to children of parent node of target node
        if ($nodeToMoveAfter->hasParent()) {
           $parentOfTargetNode = $nodeToMoveAfter->getParent();
           $parentOfTargetNode->addChildAfter($nodeToBeMoved, $nodeToMoveAfter);
           $nodeToBeMoved->setParent($parentOfTargetNode);
        } else {
            throw new Exception("Trying to move a node after a node that doesn't have a parent node! 1307646535");
        }
        $this->updateCategoryTree();
	}
	
	
	
	/**
	 * Adds a given node into a given parent node
	 *
	 * @param Tx_Yag_Domain_Model_Category $newNode Node to be added to tree
	 * @param Tx_Yag_Domain_Model_Category $parentNode Node to add new node into
	 */
	public function insertNode(Tx_Yag_Domain_Model_Category $newNode, Tx_Yag_Domain_Model_Category $parentNode) {
		$this->getNodeByUid($parentNode->getUid())->addChild($newNode);
		$newNode->setParent($this->getNodeByUid($parentNode->getUid()));
		$this->updateCategoryTree();
	}
	
	
	
	/**
	 * Initializes the tree map for this tree
	 */
	protected function initTreeMap() {
		$this->treeMap = array();
		if ($this->rootNode !== null) {
			$this->addNodeToTreeMap($this->rootNode);
			foreach ($this->rootNode->getSubCategories() as $node) {
				$this->addNodeToTreeMap($node);
			}
		}
	}
	
	
	
	/**
	 * Adds a node to tree map for this tree
	 *
	 * @param Tx_Yag_Domain_Model_Category $node Node to be added to tree map
	 */
	protected function addNodeToTreeMap(Tx_Yag_Domain_Model_Category $node) {
		$this->treeMap[$node->getUid()] = $node;
	}
	
	
	
	/**
	 * Removes a node from the tree map
	 *
	 * @param Tx_Yag_Domain_Model_Category $node Node to be removed from tree map
	 */
	protected function removeNodeFromTreeMap(Tx_Yag_Domain_Model_Category $node) {
		if (array_key_exists($node->getUid(), $this->treeMap)) {
			unset($this->treeMap[$node->getUid()]);
		}
	}
	
	
	
	/**
	 * Checks whether given node is in tree
	 *
	 * @param Tx_Yag_Domain_Model_Category $node Node to check for whether it's in the tree
	 * @param string $errMessage An error message to be displayed, if node is not in tree
	 */
	protected function checkForNodeBeingInTree(Tx_Yag_Domain_Model_Category $node, $errMessage = 'Node is not found in current tree! 1307646533 ') {
	    if (!array_key_exists($node->getUid(), $this->treeMap)) {
            throw new Exception($errMessage . ' node UID: ' . $node->getUid() . print_r(array_keys($this->treeMap),true));
        }
	}
	
	
	
	/**
	 * Updates tree after any changes took place
	 */
	protected function updateCategoryTree() {
		$this->nsTreeWalker->traverseTreeDfs($this);
	}
	
	
	
	/**
	 * Renders a category tree to a ul html element (For debugging)
	 *
	 * @return string
	 */
	public function toString() {
		return '<ul>' . $this->rootNode->toString() . '</ul>';
	}
	
}

?>