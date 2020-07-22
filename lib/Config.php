<?php

namespace bxmsksync;

/**
 * Настройки модуля bx.msksync
 *
 * @author dimabresky
 */
class Config {

    const LOGIN = "admin@grincomcom"; // login для подключения к Мой склад
    const PASSWORD = "bfb287e6dc"; // пароль для подключения Мой склад
    const CATALOG_IBLOCK_ID = 11; // ID торгового каталога
    const CATALOG_IBLOCK_TYPE = 'firstbit_beautyshop_catalog'; // Тип инфоблока торгового каталога
    const API_URL = "https://online.moysklad.ru/api/remap/1.2";
    const SYNC_ROWS_LIMIT = 80; // количество элементов получаемое от Мой склад за одну итерацию
    const VOLUME_PROPERTY_CODE = 'KOS_OBEM'; // код свойства объёма
    const REL_IMG_PATH = '/upload/bxmsksync_images'; // код свойства объёма


}
