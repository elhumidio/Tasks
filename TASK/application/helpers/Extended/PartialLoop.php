<?php
/**
 * Este Helper extiende las funcionalidades del Partial Loop para pasar las variables de la vista.
 *
 */
class Extended_Helper_PartialLoop extends Zend_View_Helper_PartialLoop
{
	public $view;

	public function __construct()
	{
		$this->setObjectKey('object');
	}

	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}

	public function ExtendedPartialLoop($path, $array)
	{
		return parent::partialLoop($path, $array);
	}

	public function partial($name, $module, $item)
	{
		$objectKey = $this->getObjectKey();
		if(is_object($item) && isset($objectKey) && $objectKey != 'view')
		{
			$item = array($objectKey => $item, 'view' => $this->view);
		}
		elseif(is_array($item) && !isset($item['view']))
		{
			$item['view'] = $this->view;
		}
		else
		{
			// Error		
		}

		return parent::partial($name, $module, $item);
	}
}
