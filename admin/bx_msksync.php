<?php

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

global $USER;

if (!$USER->isAdmin()) {
    $APPLICATION->AuthForm("Access denided.");
}

$APPLICATION->SetTitle(Loc::getMessage("BXMSKSYNC_PAGE_TITLE"));

\Bitrix\Main\Loader::includeModule("bx.msksync");

include "ajax.php";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if (!function_exists('curl_init')) {
    echo bxmsksync\SyncTools::errorHTML('Для работы модуля необходимо наличие установленной библиотеки curl на сервере.');
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    die;
}

\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.vue");

echo
BeginNote(),
 'Данный модуль синхронизирует у существующих товаров <b>Детальное изображение</b>, <b>вес</b>, <b>объём</b> c товарами из Мой склад.'
 . '<br> Данная синхронизация может занять длительное время. Не закрывайте и не перезагружайте страницу.',
 EndNote();
?>
<div class="bx-msksync">

    <?
    if (!\bxmsksync\Config::LOGIN || !\bxmsksync\Config::PASSWORD || !\bxmsksync\Config::CATALOG_IBLOCK_ID):
        CAdminMessage::ShowMessage('Для синхронизации укажите логин, пароль, id торгового каталога в файле lib/Config.php в папке модуля.');
        ?>
    <? else: ?>
        <div class="bx-msksync__progress"></div>
        <button v-if="showSyncBtn" @click="sync" class="ui-btn-main ui-btn-success">Синхронизировать</button>
    <? endif ?>
</div>
<script>
    BX.Vue.create({
        el: '.bx-msksync',
        data: {
            showSyncBtn: true,
            showProgress: false
        },
        methods: {
            sync() {
                let progressNode = document.querySelector('.bx-msksync__progress');
                this.showSyncBtn = false;
                BX.ajax.post('<?= $APPLICATION->GetCurPage() ?>', {bxmsksync: 'Y'}, response => {
                    response = JSON.parse(response);
                    if (response.error) {
                        alert(response.message);
                        this.showSyncBtn = true;
                        progressNode.innerHTML = '';
                        return;
                    }

                    progressNode.innerHTML = response.resultHTML;
                    
                    if (response.done) {
                        this.showBtnSync = true;
                        return;
                    } else {
                        this.sync();
                    }
                });
            }
        }
    });
</script>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

