<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2011 Michael Knoll <mimi@kaktusteam.de>
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
 * Abstract repository, updates pageCacheManager for automatic cache clearing
 *
 * @package Domain
 * @subpackage Repository
 * @author Daniel Lienert <daniel@lienert.cc>
 */
class Tx_Yag_Domain_Repository_AbstractRepository extends Tx_Extbase_Persistence_Repository {
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Persistence/Tx_Extbase_Persistence_Repository::add()
	 */
	public function add($object) {
		parent::add($object);
		$this->objectManager->get('Tx_Yag_PageCache_PageCacheManager')->markObjectUpdated($object);
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Persistence/Tx_Extbase_Persistence_Repository::remove()
	 */
	public function remove($object) {
		parent::remove($object);
		$this->objectManager->get('Tx_Yag_PageCache_PageCacheManager')->markObjectUpdated($object);
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Persistence/Tx_Extbase_Persistence_Repository::update()
	 */
	public function update($modifiedObject) {
		parent::update($modifiedObject);
		$this->objectManager->get('Tx_Yag_PageCache_PageCacheManager')->markObjectUpdated($modifiedObject);
	}
}
?>