<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Stanislas Rolland <stanislas.rolland@typo3.org>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Magic image service
 *
 * @author Stanislas Rolland <stanislas.rolland@typo3.org>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_file_Service_MagicImageService {

	/**
	 * @var t3lib_stdGraphic
	 */
	protected $imageObject;

	/**
	 * Internal function to retrieve the target magic image folder
	 *
	 * @param string $targetFolderCombinedIdentifier
	 * @return t3lib_file_Folder
	 */
	protected function getMagicFolder($targetFolderCombinedIdentifier) {
		$fileFactory = t3lib_file_Factory::getInstance();

			// @todo Proper exception handling is missing here
		if ($targetFolderCombinedIdentifier) {
			$magicFolder = $fileFactory->getFolderObjectFromCombinedIdentifier($targetFolderCombinedIdentifier);
		}

		if (empty($magicFolder) || !($magicFolder instanceof t3lib_file_Folder)) {
			$magicFolder = $fileFactory->getFolderObjectFromCombinedIdentifier(
				$GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_imageStorageDir']
			);
		}

		return $magicFolder;
	}

	/**
	 * Internal function to retrieve the image object,
	 * if it does not exist, an instance will be created
	 *
	 * @return t3lib_stdGraphic
	 */
	protected function getImageObject() {
		if ($this->imageObject === NULL) {
			/** @var $this->imageObject t3lib_stdGraphic */
			$this->imageObject = t3lib_div::makeInstance('t3lib_stdGraphic');
			$this->imageObject->init();
			$this->imageObject->mayScaleUp = 0;
			$this->imageObject->tempPath = PATH_site . $this->imageObject->tempPath;
		}
		return $this->imageObject;
	}

	/**
	 * Creates a magic image
	 *
	 * @param t3lib_file_FileInterface $imageFileObject: the original image file
	 * @param array $fileConfiguration (width, height, maxW, maxH)
	 * @param string $targetFolderCombinedIdentifier: target folder combined identifier
	 * @return t3lib_file_FileInterface
	 */
	public function createMagicImage(t3lib_file_FileInterface $imageFileObject, array $fileConfiguration, $targetFolderCombinedIdentifier) {
		$magicImage = NULL;

			// Get file for processing
		$imageFilePath = $imageFileObject->getForLocalProcessing(TRUE);
			// Process dimensions
		$maxWidth = t3lib_utility_Math::forceIntegerInRange($fileConfiguration['width'], 0, $fileConfiguration['maxW']);
		$maxHeight = t3lib_utility_Math::forceIntegerInRange($fileConfiguration['height'], 0, $fileConfiguration['maxH']);
		if (!$maxWidth) {
			$maxWidth = $fileConfiguration['maxW'];
		}
		if (!$maxHeight) {
			$maxHeight = $fileConfiguration['maxH'];
		}
			// Create the magic image
		$magicImageInfo = $this->getImageObject()->imageMagickConvert($imageFilePath, 'WEB', $maxWidth . 'm', $maxHeight . 'm');

		if ($magicImageInfo[3]) {
			$targetFileName = 'RTEmagicC_' . pathInfo($imageFileObject->getName(), PATHINFO_FILENAME) . '.' . pathinfo($magicImageInfo[3], PATHINFO_EXTENSION);
			$magicFolder = $this->getMagicFolder($targetFolderCombinedIdentifier);
			if ($magicFolder instanceof t3lib_file_Folder) {
				$magicImage = $magicFolder->addFile($magicImageInfo[3], $targetFileName, 'changeName');
			}
		}

		return $magicImage;
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/Service/MagicImageService.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/Service/MagicImageService.php']);
}

?>