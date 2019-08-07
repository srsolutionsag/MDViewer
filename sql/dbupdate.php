<#1>
<?php
include_once "Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerConfig.php";
ilMDViewerConfig::updateDB();
?>

<#2>
<?php
include_once "Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerConfig.php";
ilMDViewerConfig::set(
	ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES,
	["2"]
);
?>