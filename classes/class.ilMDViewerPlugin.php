<?php
include_once("./Services/COPage/classes/class.ilPageComponentPlugin.php");
require_once('./Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerPluginGUI.php');

/**
 * Class ilMDViewerPlugin
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMDViewerPlugin extends ilPageComponentPlugin {

	/**
	 * Get plugin name
	 *
	 * @return string
	 */
	public function getPluginName() {
		return "MDViewer";
	}


	/**
	 * @param string $a_parent_type
	 *
	 * @return bool
	 */
	public function isValidParentType($a_parent_type) {
		global $rbacreview, $ilUser;
		/**
		 * @var $rbacreview ilRbacReview
		 */
		if ($rbacreview->isAssigned($ilUser->getId(), 2)) {
			return true;
		}

		return false;
	}


	/**
	 * @param $a_mode
	 *
	 * @return array
	 */
	public function getJavascriptFiles() {
		return array();
	}


	/**
	 * @param $a_mode
	 *
	 * @return array
	 */
	public function getCssFiles() {
		return array(
			'templates/external-md.css',
		);
	}


	//	/**
	//	 * @param $key
	//	 * @return mixed|string
	//	 * @throws \ilException
	//	 */
	//	public function txt($key) {
	//		require_once('./Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php');
	//
	//		return sragPluginTranslator::getInstance($this)->active()->write()->txt($key);
	//	}
}

