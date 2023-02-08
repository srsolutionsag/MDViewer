<#1>
<?php
include_once "Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerConfig.php";
ilMDViewerConfig::updateDB();
?>

<#2>
<?php
include_once "Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerConfig.php";
ilMDViewerConfig::setConfigValue(
    ilMDViewerConfig::KEY_IDS_OF_AUTHORIZED_ROLES,
    ["2"]
);
?>
<#3>
<?php
include_once "Customizing/global/plugins/Services/COPage/PageComponent/MDViewer/classes/class.ilMDViewerConfig.php";
ilMDViewerConfig::setConfigValue(
    ilMDViewerConfig::KEY_MD_BLOCKS_FILTER_ACTIVE,
    true
);
?>
