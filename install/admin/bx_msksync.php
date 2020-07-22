<? 
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/bx.msksync/admin/bx_msksync.php")) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/bx.msksync/admin/bx_msksync.php");
} else {
    require($_SERVER["DOCUMENT_ROOT"] . "/local/modules/bx.msksync/admin/bx_msksync.php");
}
?>