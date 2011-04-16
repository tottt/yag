<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Michael Knoll <mimi@kaktusteam.de>
*           Daniel Lienert <daniel@lienert.cc>
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
 * Testcase for yag category
 *
 * @package Tests
 * @subpackage Domain\Model
 * @author Michael Knoll <knoll@punkt.de>
 */
class Tx_Yag_Tests_Domain_Model_CategoryTest extends Tx_Yag_Tests_BaseTestCase {
     
	/** @test */
	public function constructReturnsInitializedCategory() {
		$category = new Tx_Yag_Domain_Model_Category();
		$this->assertEquals($category->getLft(), 1);
		$this->assertEquals($category->getRgt(), 2);
	}
	
	
	
    /** @test */
	public function addChildCategoryUpdatesLeftRight() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
		$childCategory = new Tx_Yag_Domain_Model_Category();
		
		$parentCategory->addChild($childCategory);
		
		$lftRgtValues = array($parentCategory->getLft(), $childCategory->getLft(), $childCategory->getRgt(), $parentCategory->getRgt());
		
		$this->assertEquals(array(1,2,3,4), $lftRgtValues);
	}
	
	
	
	/** @test */
	public function addTwoChildCategoriesUpdatesLeftRight() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory1 = new Tx_Yag_Domain_Model_Category();
        $childCategory2 = new Tx_Yag_Domain_Model_Category();
        $parentCategory->addChild($childCategory1);
        $parentCategory->addChild($childCategory2);
        
        $this->assertEquals(array(1,2,3,4,5,6), 
            array($parentCategory->getLft(),
                  $childCategory1->getLft(), $childCategory1->getRgt(),
                  $childCategory2->getLft(), $childCategory2->getRgt(),
                  $parentCategory->getRgt()));
        
	}
	
	
	
	/** @test */
	public function addTwoChildToChildUpdatesLeftToRight() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory1 = new Tx_Yag_Domain_Model_Category();
        $childCategory2 = new Tx_Yag_Domain_Model_Category();
        
        $childCategory1->addChild($childCategory2);
        $parentCategory->addChild($childCategory1);
        
        $this->assertEquals(array(1,2,3,4,5,6), 
            array($parentCategory->getLft(),
                  $childCategory1->getLft(),
                  $childCategory2->getLft(), 
                  $childCategory2->getRgt(),
                  $childCategory1->getRgt(),
                  $parentCategory->getRgt()));
	}
	
	
	
	/** @test */
	public function removeChildUpdatesLeftToRight() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory = new Tx_Yag_Domain_Model_Category();
        $parentCategory->addChild($childCategory);
        $this->assertEquals(array(1,2,3,4), array($parentCategory->getLft(), $childCategory->getLft(), $childCategory->getRgt(), $parentCategory->getRgt()));
        
        $parentCategory->removeChild($childCategory);
        $this->assertEquals(1, $parentCategory->getLft());
        $this->assertEquals(2, $parentCategory->getRgt());
	}
	
	
	
	/** @test */
	public function getChildCountReturnsOneForOneAddedChild() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory1 = new Tx_Yag_Domain_Model_Category();
        $parentCategory->addChild($childCategory1);
        $this->assertEquals(1, $parentCategory->getChildrenCount());
	}
	
	
	
	/** @test */
	public function getChildCountReturnsTwoForTwoAddedChildren() {
	    $parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory1 = new Tx_Yag_Domain_Model_Category();
        $childCategory2 = new Tx_Yag_Domain_Model_Category();
        
        $childCategory1->addChild($childCategory2);
        $parentCategory->addChild($childCategory1);
        
        $this->assertEquals(2, $parentCategory->getChildrenCount());
	}
	
	
	
	/** @test */
	public function getChildCountReturnsZeroIfThereAreNoChildren() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
		$this->assertEquals(0, $parentCategory->getChildrenCount());
	}
	
	
	
	/** @test */
	public function hasChildrenReturnsTrueIfCategoryHasChildren() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
		$childCategory1 = new Tx_Yag_Domain_Model_Category();
		$parentCategory->addChild($childCategory1);
		$this->assertEquals(true, $parentCategory->hasChildren());
	}
	
	
	
	/** @test */
	public function hasChildrenReturnsFalseIfCategoryHasNoChildren() {
		$parentCategory = new Tx_Yag_Domain_Model_Category();
		$this->assertEquals(false, $parentCategory->hasChildren());
	}
	
	
	
    /** @test */
    public function getLevelReturnsTwoIfChildOfChild() {
        $parentCategory = new Tx_Yag_Domain_Model_Category();
        $childCategory1 = new Tx_Yag_Domain_Model_Category();
        $childCategory2 = new Tx_Yag_Domain_Model_Category();
        
        $childCategory1->addChild($childCategory2);
        $parentCategory->addChild($childCategory1);
        
        $this->assertEquals(2, $childCategory2->getLevel());
    }
	
}

?>