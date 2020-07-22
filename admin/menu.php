<?
IncludeModuleLangFile(__FILE__);

$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section" => "bx_msksync",
    "sort" => 10000,
    "text" => GetMessage("BXMSKSYNC_MENU_TITLE"),
    "title" => GetMessage("BXMSKSYNC_MENU_TITLE"),
    "icon" => "bx_msksync-20x20",
    "page_icon" => "bx_msksync",
    "items_id" => "menu_bx_msksync",
    "url" => "bx_msksync.php?lang=" . LANGUAGE_ID
);

return $aMenu;
?>