<?php

namespace bxmsksync;

/**
 * @author dimabresky
 */
class SyncTools {

    public static function sendJsonResponse(array $data) {
        global $APPLICATION;
        \header('Content-Type: application/json; charset=' . \SITE_CHARSET);
        $APPLICATION->RestartBuffer();
        echo json_encode($data);
        die();
    }

    public static function resultHTML(array $parameters) {
        ob_start();
        \CAdminMessage::ShowMessage($parameters);
        return ob_get_clean();
    }

    public static function progressHTML(int $progress) {
        return self::resultHTML([
            "MESSAGE" => "Идет синхронизация...",
            "DETAILS" => "#PROGRESS_BAR#",
            "HTML" => true,
            "TYPE" => "PROGRESS",
            "PROGRESS_TOTAL" => 100,
            "PROGRESS_VALUE" => $progress,
        ]);
    }

    public static function errorHTML(string $message = "Не удалось получить токен доступа.") {
        return self::resultHTML([
            "MESSAGE" => $message,
            "TYPE" => "ERROR",
            "HTML" => true
        ]);
    }

    public static function doneHTML() {
        return self::resultHTML([
            "MESSAGE" => "Синхронизация успешно завершена. Вернуться в <a href='/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=" . \bxmsksync\Config::CATALOG_IBLOCK_ID . "&type=" . \bxmsksync\Config::CATALOG_IBLOCK_TYPE . "&lang=ru&find_section_section=0&SECTION_ID=0'>каталог</a>",
            "TYPE" => "OK",
            "HTML" => true
        ]);
    }

    public static function download(string $token, string $url, string $outputFileName) {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
            ),
        ));

        $result = curl_exec($curl);

        $info = curl_getinfo($curl);
        curl_close($curl);

        $filePathForSave = self::getSaveFilePath($outputFileName);

        if ($info['redirect_url']) {
            file_put_contents($filePathForSave, file_get_contents($info['redirect_url']));
        } else {
            file_put_contents($filePathForSave, $result);
        }

        if (file_exists($filePathForSave)) {
            return $filePathForSave;
        }

        return false;
    }

    public static function getSaveFilePath(string $filename) {

        $io = \CBXVirtualIo::getInstance();
        $absPath = $io->RelativeToAbsolutePath(Config::REL_IMG_PATH);
        if (!file_exists($absPath)) {
            $io->CreateDirectory($absPath);
            file_put_contents($io->RelativeToAbsolutePath(Config::REL_IMG_PATH . "/.htaccess"), "Deny from all");
        }

        return "$absPath/$filename";
    }

}
