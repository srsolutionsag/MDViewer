<?php
include_once("./Services/COPage/classes/class.ilPageComponentPlugin.php");

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
	function getPluginName() {
		return "MDViewer";
	}


	/**
	 * @param string $a_parent_type
	 * @return bool
	 */
	public function isValidParentType($a_parent_type) {
		return true;
	}


	/**
	 * @param $a_mode
	 * @return array
	 */
	public function getJavascriptFiles($a_mode) {
		return array();
	}


	/**
	 * @param $a_mode
	 * @return array
	 */
	public function getCssFiles($a_mode) {
		switch ($a_mode) {
			case 'presentation':
				return array(
					'templates/external-md.css',
				);
		}
	}


	/**
	 * @param $key
	 * @return mixed|string
	 * @throws \ilException
	 */
	public function txt($key) {
		require_once('./Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php');

		return sragPluginTranslator::getInstance($this)->active()->write()->txt($key);
	}
}

