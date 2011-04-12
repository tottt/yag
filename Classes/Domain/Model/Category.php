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
     * The constructor.
     *
     * @return void
     */
    public function __construct() {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    
    
    /**
     * Initializes all Tx_Extbase_Persistence_ObjectStorage instances.
     *
     * @return void
     */
    protected function initStorageObjects() {

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
    public function setRoot($root) {
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
    public function setlft($lft) {
    	$this->lft = $lft;
    }
	
}

?>