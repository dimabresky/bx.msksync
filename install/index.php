<?php

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class bx_msksync extends CModule {

    public $MODULE_ID = "bx.msksync";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";

    function __construct() {
        $arModuleVersion = array();
        $path_ = str_replace("\\", "/", __FILE__);
        $path = substr($path_, 0, strlen($path_) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = Loc::getMessage("BXMSKSYNC_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BXMSKSYNC_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("BXMSKSYNC_PARTNER_NAME");
        $this->PARTNER_URI = "https://github.com/dimabresky/";

        set_time_limit(0);
    }

    public function copyFiles() {

        CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true
        );
    }

    public function copyAdminThemes() {
        CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true
        );
    }

    public function deleteAdminThemes() {
        DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID);
        DeleteDirFilesEx("/bitrix/themes/.default/" . $this->MODULE_ID . ".css");
    }


    public function DoInstall() {
        try {

            # регистрируем модуль
            ModuleManager::registerModule($this->MODULE_ID);

            # копирование файлов
            $this->copyFiles();

            # копирование стилей admin панели
            $this->copyAdminThemes();

        } catch (Exception $ex) {

            $GLOBALS["APPLICATION"]->ThrowException($ex->getMessage());

            $this->DoUninstall();

            return false;
        }

        return true;
    }

    public function DoUninstall() {

        # удаление стилей admin панели
        $this->deleteAdminThemes();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

}
