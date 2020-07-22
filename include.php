<?php

$classes = array(
    "\\bxmsksync\\Config" => "lib/Config.php",
    "\\bxmsksync\\SyncTools" => "lib/SyncTools.php"
);


CModule::AddAutoloadClasses("bx.msksync", $classes);

\Bitrix\Main\Loader::includeModule("iblock");
