<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Daniel Lienert <daniel@lienert.cc>, Michael Knoll <mimi@kaktusteam.de>
*  All rights reserved
*
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
 * Repository for category tree
 *
 * @package Domain
 * @subpackage Import
 * @author Michael Knoll <mimi@kaktusteam.de>
 */
class Tx_Yag_Domain_Repository_CategoryTreeRepository {

	/**
	 * Holds an instance of category repository
	 *
	 * @var Tx_Yag_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;
	
	
	/**
	 * Holds a reference for a tree builder
	 *
	 * @var Tx_Yag_Domain_Model_CategoryTreeBuilder
	 */
	protected $treeBuilder;
	
	
	public function __construct() {
		$this->categoryRepository = new Tx_Yag_Domain_Repository_CategoryRepository();
		$this->treeBuilder = new Tx_Yag_Domain_Model_CategoryTreeBuilder($this->categoryRepository);
	}
	
	
	
	/**
	 * Returns a category tree for a given root node id
	 *
	 * @param int $rootNodeId
	 * @return Tx_Yag_Domain_Model_CategoryTree
	 */
	public function findByRootId($rootNodeId) {
		$rootNode = $this->categoryRepository->findByUid($rootNodeId);
		return $this->treeBuilder->buildTreeForCategory($rootNode);
	}
	
	
	
	/**
	 * Returns a category tree for an arbitrary category within this tree.
	 * 
	 * @param Tx_Yag_Domain_Model_Category $category Category to return category tree for
	 * @return Tx_Yag_Domain_Model_CategoryTree
	 */
	public function findByCategory(Tx_Yag_Domain_Model_Category $category) {
		return $this->findByRootId($category->getRoot()); 
	}
	
	
	
	/**
	 * Returns a category tree for an arbitrary category uid within this tree.
	 *
	 * @param int $categoryUid UID of category to build tree for
	 * @return Tx_Yag_Domain_Model_CategoryTree
	 */
	public function findByCategoryId($categoryUid) {
		$category = $this->categoryRepository->findByUid($categoryUid);
		return $this->findByCategory($category);
	}
	
	
	
	/**
	 * Updates all categories of a tree in the category repository
	 *
	 * @param Tx_Yag_Domain_Model_CategoryTree $categoryTree Category tree to be updated in database
	 */
	public function update(Tx_Yag_Domain_Model_CategoryTree $categoryTree) {
		$this->removeDeletedNodesOfGivenCategoryTree($categoryTree);
		$categories = $categoryTree->getRoot()->getSubCategories();
		foreach ($categories as $category) {
			$this->categoryRepository->update($category);
		}
		$this->categoryRepository->update($categoryTree->getRoot());
	}
	
	
	
	/**
	 * Removes deleted nodes of a given tree from repository
	 *
	 * @param Tx_Yag_Domain_Model_CategoryTree $categoryTree Tree whose deleted nodes should be removed from repository
	 */
	protected function removeDeletedNodesOfGivenCategoryTree(Tx_Yag_Domain_Model_CategoryTree $categoryTree) {
		foreach ($categoryTree->getDeletedNodes() as $deletedNode) {
			$this->categoryRepository->remove($deletedNode);
		}
	}
	
}
 
?>