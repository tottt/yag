<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Michael Knoll <mimi@kaktusteam.de>
*           Daniel Lienert <daniel@lienert.cc>
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
 * Repository for Tx_Yag_Domain_Model_Category
 *
 * @package Domain
 * @subpackage Repository
 * @author Michael Knoll <mimi@kaktusteam.de>
 */
class Tx_Yag_Domain_Repository_CategoryRepository
    extends Tx_Yag_Domain_Repository_AbstractRepository 
    implements Tx_Yag_Domain_Model_NodeRepositoryInterface { 

	/**
	 * Adds a category (and all sub categories) to repository
	 *
	 * @param Tx_Yag_Domain_Model_Category $category
	 */
	public function add($category) {
		$categoriesToAdd = new Tx_Extbase_Persistence_ObjectStorage();
		$categoriesToAdd->attach($category);
		$categoriesToAdd->addAll($category->getSubCategories());
		
		foreach($categoriesToAdd as $categoryToPersist) {
			if ($categoryToPersist->_isNew()) {
				parent::add($categoryToPersist); 
			} else {
				parent::update($categoryToPersist);
			}
		}
	}
	
	
	
	/**
	 * Returns a category (and its sub-categories) for a given category uid
	 *
	 * @param int $uid
	 * @return Tx_Yag_Domain_Model_Category
	 */
	public function findByUid($uid) {
		$rootCategory = parent::findByUid($uid);
		$childCategories = new Tx_Extbase_Persistence_ObjectStorage();
		
		foreach($this->findChildCategoriesByRootCategory($rootCategory) as $childCategory) {
			$childCategories->attach($childCategory);
		}
		
        $this->buildUpCategoryTree($rootCategory, $childCategories);
        return $rootCategory;
	}
	
	
	
	/**
	 * Returns a set of categories determined by the root of the given node.
	 *
	 * @param Tx_Yag_Domain_Model_NodeInterface $category
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Yag_Domain_Model_Category>
	 */
	public function findByRootOfGivenNodeUid(Tx_Yag_Domain_Model_NodeInterface $category) {
		$rootUid = $category->getRoot();
		$query = $this->createQuery();
		$query->matching($query->equals('root', $rootUid))
		    ->setOrderings(array('lft' => Tx_Extbase_Persistence_Query::ORDER_DESCENDING));
		return $query->execute();
	}
	
	
	
	/**
	 * We build up a category tree recursively.
	 *
	 * @param Tx_Yag_Domain_Model_Category $root
	 * @param Tx_Extbase_Persistence_ObjectStorage $children
	 * @return unknown
	 */
	protected function buildUpCategoryTree(Tx_Yag_Domain_Model_Category $root, $children) {
		foreach ($children as $child) { /* @var $child Tx_Yag_Domain_Model_Category */
			if ($child->getRgt() < $root->getRgt()) {
				/* Current child must be child of current root - so we add it */
                $root->addChild($child, false);
                $children->detach($child);
                if ($child->getLft() + 1 == $child->getRgt()) {
                	/* Current child is a leaf. So we added it to root as a child and go on with possible next child */
                	$this->buildUpCategoryTree($root, $children);
                } else {
                	/* Current child is not a leaf. So we added it to root as a child and move down the tree */
                	$this->buildUpCategoryTree($child, $children); 
                }
			} else {
				/* Current child does not belong to current root. So we move up the tree */
				$this->buildUpCategoryTree($root->getParent(), $children);    
			}
		}
	}
	
	
	
	/**
	 * Returns a flat list of child categories for a given category
	 *
	 * @param Tx_Yag_Domain_Model_Category $rootCategory
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	public function findChildCategoriesByRootCategory(Tx_Yag_Domain_Model_Category $rootCategory) {
		$query = $this->createQuery();
		$query->matching(
		    $query->logicalAnd(
		        $query->logicalAnd(
		            $query->greaterThan('lft', $rootCategory->getLft()),$query->lessThan('rgt', $rootCategory->getRgt())),
		            $query->equals('root', $rootCategory->getRoot())
		        )
		    );
		$query->setOrderings(array('lft' => Tx_Extbase_Persistence_Query::ORDER_ASCENDING));
		return $query->execute()->toArray();
	}
	
	
	
	/**
	 * Removes a category and its subcategories
	 *
	 * @param Tx_Yag_Domain_Model_Category $category Category to be removed
	 */
	public function remove($category) {
		
		/*
		 * WARNING Whenever this goes productive, we have to make sure, 
		 * that the category table is write locked, while we
		 * do the following three queries!
		 */
		
		// We delete all database records that are no longer required
		$this->deleteCategory($category);
		
		// We update object structure
		if (!$category->isRoot()) {
			/**
			 * What happens here:
			 * 
			 * If we delete a node from the tree, there will be a "gap" in the lft - rgt numbers. We "fill" this gap by
			 * subtracting the difference (rgt - lft + 1)
			 * 1. from nodes rgt & lft number if node has a bigger lft number than deleted node
			 * 2. from nodes rgt number, if node has a smaller lft and a bigger rgt number than deleted node
			 * then the node we want to delete.
			 * Afterwards, everything is fine again.
			 */
			$left = $category->getLft();
			$right = $category->getRgt();
			$difference = intval($right - $left + 1);
			
			// We update case 1. from above
			$query1 = "UPDATE tx_yag_domain_model_category " . 
			          "SET lft = lft - " . $difference . ", rgt = rgt - " . $difference . " " . 
			          "WHERE root = " . $category->getRoot() . " " .
			          "AND lft > " . $category->getLft();
			#echo "Update 1: " . $query1;
            $extQuery1 = $this->createQuery();
            $extQuery1->getQuerySettings()->setReturnRawQueryResult(true); // Extbase WTF
            $extQuery1->statement($query1)->execute();
            
			// We update case 2. from above
			$query2 = "UPDATE tx_yag_domain_model_category " . 
			          "SET rgt = rgt - " . $difference . " " .
			          "WHERE root = " . $category->getRoot() . " " .
			          "AND lft < " . $category->getLft() . " " .
			          "AND rgt > " . $category->getRgt();
			#echo "Update 2: " . $query2;
            $extQuery2 = $this->createQuery();
            $extQuery2->getQuerySettings()->setReturnRawQueryResult(true); // Extbase WTF
            $extQuery2->statement($query2)->execute();
		}
	}
	
	
	
	/**
	 * Hard-deletes a category and its subcategories from database.
	 * No deleted=1 is set, categories are really deleted!
	 *
	 * @param Tx_Yag_Domain_Model_Category $category
	 */
	protected function deleteCategory(Tx_Yag_Domain_Model_Category $category) {
        $left = $category->getLft();
        $right = $category->getRgt();
        
        $query = "DELETE FROM tx_yag_domain_model_category WHERE lft >= " . $left . " AND rgt <= " . $right;
        #echo "DELTE query: " . $query;
        $extQuery = $this->createQuery();
        $extQuery->getQuerySettings()->setReturnRawQueryResult(true); // Extbase WTF
        $extQuery->statement($query)->execute();
	}
	
	
	
	/**
	 * Updates whole tree for any given category
	 *
	 * @param Tx_Yag_Domain_Model_Category $category Category whose tree should be updated
	 */
	public function updateTree(Tx_Yag_Domain_Model_Category $category) {
		$root = $this->findByUid($category->getRoot());
		$allCategoriesOfTree = $root->getSubCategories();
		foreach($allCategoriesOfTree as $categoryToBeUpdated) {
			$this->update($categoryToBeUpdated);
		}
	}
	
}

?>