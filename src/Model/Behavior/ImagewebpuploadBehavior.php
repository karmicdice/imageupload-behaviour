<?php
namespace App\Model\Behavior;
/**
  * 
  * Distributed under:
  * MIT License
  * 
  * Copyright (c) 2019 Karma Dice
  *	Permission is hereby granted, free of charge, to any person obtaining a copy
  * of this software and associated documentation files (the "Software"), to deal
  * in the Software without restriction, including without limitation the rights
  * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  * copies of the Software, and to permit persons to whom the Software is
  * furnished to do so, subject to the following conditions:
  * 
  * The above copyright notice and this permission notice shall be included in all
  * copies or substantial portions of the Software.
  * 
  * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
  * SOFTWARE.
  * @copyright Copyright (c) Karma Dice (https://github.com/karmicdice/)
  * @since     0.0.1
  * @license   https://opensource.org/licenses/mit-license.php MIT License
 *
 **/

use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Psr\Http\Message\UploadedFileInterface;
use Cake\Validation\ValidatorAwareTrait;

class ImagewebpuploadBehavior extends Behavior
{

	/**
	 * Set defaults
	**/

	protected $_defaultConfig = [
		'field' => 'image'
	];

	/***
	 * Can be over-written using the setter methods.
	**/
	private $_uploadLocation = WWW_ROOT . 'img' . DS . 'uploads' . DS;
	private $_publicPath = DS . 'img' . DS . 'uploads' . DS;

	/**
	 *
	 * @param $imageArray Array The uploaded file array
	 * @param $imageResource Resource Image file resource
	 * 
	**/

	private function _processImage($imageArray, $imageResource)
	{
		$filename = $this->_getFileName($imageArray, $imageResource);
		$publicPath = $this->getPublicPath() . $filename;
		$fullPathToSave = $this->getUploadLocation() . $filename;
		imagewebp($imageResource, $fullPathToSave, 70);
		return $publicPath;
	}

	/**
	 *
	 * @param $location String Location to upload the file, from server root.
	 * 
	**/

	public function setUploadLocation($location)
	{
		$this->_uploadLocation = $location;
	}

	public function getUploadLocation()
	{
		return $this->_uploadLocation;
	}

	/**
	 *
	 * @param $path Location to display / store in database, from web root.
	 * 
	**/

	public function setPublicPath($path)
	{
		$this->_publicPath = $path;
	}

	public function getPublicPath()
	{
		return $this->_publicPath;
	}

	/**
	 * @param $filename String Accepts file name. Increments a +1.
	**/

	private function _createNextFile($filename)
	{
		$fileNameArray = explode('.', $filename);
		// Stick together, incase the file has multiple dots.
		$fileNameWithoutExtension = implode('', $fileNameArray);
		// if it doesn't have a hyphen, add hyphen and add a number.
		if(strpos($fileNameWithoutExtension, '-') === false) {
			// return "$fileNameWithoutExtension-1.".$fileNameExtension; ***
			return "$fileNameWithoutExtension-1.webp";
		}
		$hyphenatedArray = explode('-', $fileNameWithoutExtension);
		$lastPart = end($hyphenatedArray);
		if(is_numeric($lastPart)) {
			$lastPart++;
		}
		array_pop($hyphenatedArray);
		array_push($hyphenatedArray, $lastPart);
		$fileName = implode('-', $hyphenatedArray);
		if(file_exists($this->getUploadLocation() . $fileName)) {
			$this->_createNextFile($fileName);
		}
		return $fileName;
	}

	private function _getFileName($imageArray, $imageResource)
	{
		if($imageResource === true) {
			if(file_exists($this->getUploadLocation() . $imageArray['name']))
			{
				return $this->_createNextFile($imageArray['name']);
			} else {
				return $imageArray['name'];
			}
		}
		$explodedFileName = explode('.', $imageArray['name']);
		array_pop($explodedFileName);
		$filename = implode('', $explodedFileName) . '.webp';
		if(file_exists($this->getUploadLocation() . $filename))
		{
			$filename = $this->_createNextFile($filename);
		}
		return $filename;
	}

	public function _getImage($imageArray)
	{
		if(!function_exists('imagewebp'))
		{
			die('Please install PHP GD library');
		}

		$imageResource = true;
		switch (mime_content_type($imageArray['tmp_name'])) {
			case 'image/jpeg':
				$imageResource = imagecreatefromjpeg($imageArray['tmp_name']);
				break;
			case 'image/png':
				$imageResource = imagecreatefrompng($imageArray['tmp_name']);
				break;
			case 'image/webp':
				$imageResource = true;
				break;
			default:
				throw new \Exception(401);
				break;
		}
		return $this->_processImage($imageArray, $imageResource);
	}

	/**
	 * Call beforeMarshal to change the request data before it gets converted to object.
	 * @param $event Event
     * @param $data ArrayObject
     * @param $options ArrayObject
	**/

	public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
	{
		$config = $this->getConfig();
		if($data[$config['field']]['error'] == 0)
		{
			$image = $this->_getImage($data[$config['field']]);
			$data[$config['field']] = $image;
		} else {
			unset($data[$config['field']]);
		}
	}
}

