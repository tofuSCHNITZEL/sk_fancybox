<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'class.tx_tpfancybox2.php', '', 'includeLib', 1);
?>