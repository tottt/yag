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
 * Class implements Category domain object
 *
 * @package Domain
 * @subpackage Model
 * @author Michael Knoll <mimi@kaktusteam.de>
 * @author Daniel Lienert <daniel@lienert.cc>
 */
class Tx_Yag_Domain_Model_Category extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
     * Name for category
     *
     * @var string $name
     */
    protected $name;
    
    

    /**
     * Description for category
     *
     * @var string $description
     */
    protected $description;
    
    

    /**
     * Number of first visit of node in category tree
     *
     * @var int $lft
     */
    protected $lft;
    
    

    /**
     * Number of second visit of node in category tree
     *
     * @var int $rgt
     */
    protected $rgt;
    
    

    /**
     * ID of root node in category tree
     *
     * @var int $root
     */
    protected $root;
    
    
    
    /**
     * Holds refernce to parent category (null, if root)
     *
     * @var Tx_Yag_Domain_Model_Category
     */
    protected $parent;
    
    
    
    /**
     * Holds references to child categories
     *
     * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Yag_Domain_Model_Category>
     */
    protected $children;
    
    
    
    /**
     * The constructor.
     *
     * @return void
     */
    public function __construct() {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
        
        // We initialize lft and rgt as those values will be overwritten later, if this is not the root node
        $this->lft = 1;
        $this->rgt = 2;
    }

    
    
    /**
     * Initializes all Tx_Extbase_Persistence_ObjectStorage instances.
     *
     * @return void
     */
    protected function initStorageObjects() {
        $this->children = new Tx_Extbase_Persistence_ObjectStorage();
    }
    
    

    /**
     * Setter for name
     *
     * @param string $name Name for category
     * @return void
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    

    /**
     * Getter for name
     *
     * @return string Name for category
     */
    public function getName() {
        return $this->name;
    }


    
    /**
     * Setter for description
     *
     * @param string $description Description for category
     * @return void
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    
    

    /**
     * Getter for description
     *
     * @return string Description for category
     */
    public function getDescription() {
        return $this->description;
    }
    
    
    
    /**
     * Getter for root category
     *
     * @return int
     */
    public function getRoot() {
    	return $this->root;
    }
    
    
    
    /**
     * Setter for root category
     *
     * @param int $root
     */
    public function setRoot(Tx_Yag_Domain_Model_Category $root) {
    	$this->root = $root;
    }
    
    
    
    /**
     * Getter for second visit in category tree
     *
     * @return int
     */
    public function getRgt() {
    	return $this->rgt;
    }
    
    
    
    /**
     * Setter for second visit in category tree
     *
     * @param int $rgt
     */
    public function setRgt($rgt) {
    	$this->rgt = $rgt; 
    }
    
    
    
    /**
     * Getter for first visit in category tree
     *
     * @return int
     */
    public function getLft() {
    	return $this->lft;
    }
    
    
    
    /**
     * Setter for first visit in category tree
     *
     * @param int $lft
     */
    public function setLft($lft) {
    	$this->lft = $lft;
    }
    
    
    
    /**
     * Setter for parent category
     *
     * @param Tx_Yag_Domain_Model_Category $category
     */
    public function setParent(Tx_Yag_Domain_Model_Category $category) {
    	$this->parent = $category;
    	$category->addChild($this);
    	$category->updateLeftRight();
    }
    
    
    
    /**
     * Getter for parent category
     *
     * @return Tx_Yag_Domain_Model_Category
     */
    public function getParent() {
    	return $this->parent;	
    }
    
    
    
    /**
     * Adds a child category to children at end of children
     *
     * @param Tx_Yag_Domain_Model_Category $category
     */
    public function addChild(Tx_Yag_Domain_Model_Category $category) {
    	$this->children->attach($category);
    	$this->updateLeftRight();
    }
    
    
    
    /**
     * Adds a new child category after a given child category
     *
     * @param Tx_Yag_Domain_Model_Category $newChildCategory
     * @param Tx_Yag_Domain_Model_Category $categoryToAddAfter
     */
    public function addChildAfter(Tx_Yag_Domain_Model_Category $newChildCategory, Tx_Yag_Domain_Model_Category $categoryToAddAfter) {
    	$newChildren = new Tx_Extbase_Persistence_ObjectStorage();
    	foreach ($this->children as $child) {
    		$newChildren->attach($child);
    		if ($child->getId() == $categoryToAddAfter->getId()) {
    			$newChildren->add($newChildCategory);
    		}
    	}
    	$this->children = $newChildren;
    	$this->updateLeftRight();
    }
    
    
    
    /**
     * Adds a new child category before a given child category
     *
     * @param Tx_Yag_Domain_Model_Category $newChildCategory
     * @param Tx_Yag_Domain_Model_Category $categoryToAddBefore
     */
    public function addChildBefore(Tx_Yag_Domain_Model_Category $newChildCategory, Tx_Yag_Domain_Model_Category $categoryToAddBefore) {
    	$newChildren = Tx_Extbase_Persistence_ObjectStorage();
    	foreach($this->children as $child) {
    		if ($child->getId() == $categoryToAddBefore->getId()) {
    			$newChildren->attach($newChildCategory);
    		}
    		$newChildren->add($child);
    	}
    	$this->children = $newChildren;
    	$this->updateLeftRight();
    }
    
    
    
    /**
     * Removes given child category
     *
     * @param Tx_Yag_Domain_Model_Category $child
     */
    public function removeChild(Tx_Yag_Domain_Model_Category $child) {
    	$this->children->detach($child);
    	$this->updateLeftRight(); 
    }
    
    
    
    /**
     * Getter for child categories
     *
     * @return Tx_Extbase_Persistence_ObjectStorage
     */
    public function getChildren() {
    	return $this->children;
    }
    
    
    
    /**
     * Updates lft and rgt of this category and its sub-categories
     *
     * @param int $left
     * @return int Next left value after updating this node
     */
    protected function updateLeftRight($left = 0) {
    	if ($left == 0) {
    		$left = $this->lft;
    	} else {
    		$this->lft = $left;
    	}
    	$left++;
    	if ($this->hasChildren()) {
	    	foreach ($this->children as $childNode) {
	    		$left = $childNode->updateLeftRight($left);
	    	}
    	}
    	$this->rgt = $left;
    	return ++$left;
    }
    
    
    
    /**
     * Returns true, if category has children
     *
     * @return bool
     */
    public function hasChildren() {
    	return $this->getChildrenCount() > 0;
    }
    
    
    
    /**
     * Get count of children recursively
     *
     * @return int
     */
    public function getChildrenCount() {
    	$count = 0;
    	foreach ($this->children as $child) {
    		$count++;
    		$count += $child->getChildrenCount();
    	}
    	return $count;
    }
    
}

?>