<?php
include_once("./Services/COPage/classes/class.ilPageComponentPluginGUI.php");

/**
 * Class ilMDViewerPluginGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilMDViewerPluginGUI: ilPCPluggedGUI
 */
class ilMDViewerPluginGUI extends ilPageComponentPluginGUI {

	const F_EXTERNAL_MD = 'external_md';


	public function executeCommand() {
		global $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */

		$next_class = $ilCtrl->getNextClass();

		switch ($next_class) {
			default:
				// perform valid commands
				$cmd = $ilCtrl->getCmd();
				if (in_array($cmd, array(
					"create",
					"save",
					"edit",
					"edit2",
					"update",
					"cancel",
				))) {
					$this->$cmd();
				}
				break;
		}
	}


	public function insert() {
		global $tpl;

		$form = $this->initForm('create');
		$tpl->setContent($form->getHTML());
	}


	public function create() {
		global $tpl;

		$form = $this->initForm('create');
		if ($form->checkInput()) {
			$properties = array(
				self::F_EXTERNAL_MD => $form->getInput(self::F_EXTERNAL_MD),
			);
			if ($this->createElement($properties)) {
				ilUtil::sendSuccess($this->getPlugin()->txt("msg_saved"), true);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();

		$tpl->setContent($form->getHTML());
	}


	public function edit() {
		global $tpl;

		$form = $this->initForm('update');
		$form->setValuesByArray($this->getProperties());
		$tpl->setContent($form->getHTML());
	}


	public function update() {
		global $tpl;

		$form = $this->initForm('update');
		if ($form->checkInput()) {
			$properties = array(
				self::F_EXTERNAL_MD => $form->getInput(self::F_EXTERNAL_MD),
			);
			if ($this->updateElement($properties)) {
				ilUtil::sendSuccess($this->getPlugin()->txt("msg_saved"), true);
				$this->returnToParent();
			}
		}

		$form->setValuesByPost();

		$tpl->setContent($form->getHTML());
	}


	public function cancel() {
		$this->returnToParent();
	}


	/**
	 * Get HTML for element
	 *
	 * @param       $a_mode
	 * @param array $a_properties
	 * @param       $a_plugin_version
	 *
	 * @return mixed
	 */
	public function getElementHTML($a_mode, array $a_properties, $a_plugin_version) {
		require_once('./Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/vendor/autoload.php');
		$p = new Parsedown();

		return "<div class='external-md'>"
		       . $p->text(file_get_contents($a_properties[self::F_EXTERNAL_MD])) . "</div>";
	}


	/**
	 * @param string $mode create or update
	 * @return \ilPropertyFormGUI
	 */
	protected function initForm($mode = 'create') {
		global $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */
		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->getPlugin()->txt('form_title'));
		$form->addCommandButton($mode, $this->getPlugin()->txt("form_button_" . $mode));
		$form->addCommandButton("cancel", $this->getPlugin()->txt("form_button_cancel"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		$md = new ilTextInputGUI($this->getPlugin()->txt('form_md'), self::F_EXTERNAL_MD);
		$form->addItem($md);

		return $form;
	}
}