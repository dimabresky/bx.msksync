<?php

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->isPost() && $request->isAjaxRequest() && $request->get('bxmsksync') == 'Y') {
    @set_time_limit(0);
    @ignore_user_abort(true);

    if (!isset($_SESSION['bxmsksync_parameters'])) {
        $_SESSION['bxmsksync_parameters'] = [
            'access_token' => null,
            'offset' => 0,
            'total_count' => CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => \bxmsksync\Config::CATALOG_IBLOCK_ID, "ACTIVE" => "Y"), Array(), false, Array())
        ];

        $resultHTML = '';
        if (!$_SESSION['bxmsksync_parameters']['total_count']) {
            $resultHTML = bxmsksync\SyncTools::doneHTML();
        } else {
            $resultHTML = bxmsksync\SyncTools::progressHTML(0);
        }

        bxmsksync\SyncTools::sendJsonResponse([
            'error' => false,
            'done' => !$_SESSION['bxmsksync_parameters']['total_count'],
            'resultHTML' => $resultHTML
        ]);
    }

    $httpClient = new \Bitrix\Main\Web\HttpClient;

    if (!$_SESSION['bxmsksync_parameters']['access_token']) {
        $accessTokenResponse = $httpClient->setAuthorization(\bxmsksync\Config::LOGIN, \bxmsksync\Config::PASSWORD)->post(\bxmsksync\Config::API_URL . "/security/token");
        if (!$accessTokenResponse || !($accessTokenData = json_decode($accessTokenResponse, true))) {
            bxmsksync\SyncTools::sendJsonResponse([
                'error' => false,
                'done' => true,
                'resultHTML' => bxmsksync\SyncTools::errorHTML()
            ]);
        }
        $_SESSION['bxmsksync_parameters']['access_token'] = $accessTokenData['access_token'];
    }

    $accessToken = $_SESSION['bxmsksync_parameters']['access_token'];
    $productsListResponse = $httpClient->setHeader("Authorization", "Bearer $accessToken")->get(\bxmsksync\Config::API_URL . "/entity/product?expand=images&limit=" . \bxmsksync\Config::SYNC_ROWS_LIMIT . "&offset=" . $_SESSION['bxmsksync_parameters']['offset']);

    if ($productsListResponse) {
        $productsList = json_decode($productsListResponse, true);

        if (empty($productsList['rows'])) {
            unset($_SESSION['bxmsksync_parameters']);
            bxmsksync\SyncTools::sendJsonResponse([
                'error' => false,
                'done' => true,
                'resultHTML' => bxmsksync\SyncTools::doneHTML()
            ]);
        }

        $syncData = [];

        foreach ($productsList['rows'] as $row) {
            $syncData[$row['externalCode']] = [
                'images' => @$row['images'],
                'weight' => $row['weight'],
                'volume' => $row['volume'],
            ];
        }

        $filter = [
            'IBLOCK_ID' => \bxmsksync\Config::CATALOG_IBLOCK_ID,
            'XML_ID' => array_values(array_keys($syncData)),
            'ACTIVE' => 'Y'
        ];

        $oCIBlockElement = new CIBlockElement;
        $dbElements = $oCIBlockElement->GetList(false, $filter, false, false, ['ID', 'XML_ID', 'DETAIL_PICTURE']);

        while ($element = $dbElements->Fetch()) {

            CIBlockElement::SetPropertyValuesEx($element['ID'], \bxmsksync\Config::CATALOG_IBLOCK_ID, [\bxmsksync\Config::VOLUME_PROPERTY_CODE => $syncData[$element['XML_ID']]['volume']]);
            \Bitrix\Catalog\ProductTable::update($element['ID'], ['WEIGHT' => $syncData[$element['XML_ID']]['weight']]);
            $detailPicture = null;
            if ($element['DETAIL_PICTURE'] > 0) {
                $detailPicture = CFile::GetFileArray($element['DETAIL_PICTURE']);
            }

            if ($syncData[$element['XML_ID']]['images'] && $syncData[$element['XML_ID']]['images']['rows'] && is_array($syncData[$element['XML_ID']]['images']['rows'])) {

                $imageData = $syncData[$element['XML_ID']]['images']['rows'][0];
                if ($imageData['filename'] && $imageData['meta']['downloadHref']) {
                    $filename = $imageData['filename'];

                    if (!$detailPicture || $detailPicture['ORIGINAL_NAME'] != $filename) {

                        $href = $imageData['meta']['downloadHref'];

                        $saveFilePath = \bxmsksync\SyncTools::download($accessToken, $href, $filename);
                        if ($saveFilePath) {
                            $imgArr = CFile::MakeFileArray($saveFilePath);
                            $arUpdate = [
                                'DETAIL_PICTURE' => $imgArr,
                                'PREVIEW_PICTURE' => $imgArr,
                            ];

                            if ($element['DETAIL_PICTURE'] > 0) {
                                $arUpdate['DETAIL_PICTURE']['del'] = 'Y';
                                $arUpdate['PREVIEW_PICTURE']['del'] = 'Y';
                            }

                            $oCIBlockElement->Update($element['ID'], $arUpdate);

                            unlink($saveFilePath);
                        }
                    }
                }
            }
        }
        $_SESSION['bxmsksync_parameters']['offset'] += \bxmsksync\Config::SYNC_ROWS_LIMIT;
        $progress = ceil(100 * $_SESSION['bxmsksync_parameters']['offset'] / $_SESSION['bxmsksync_parameters']['total_count']);

        if ($progress >= 100) {
            unset($_SESSION['bxmsksync_parameters']);
            bxmsksync\SyncTools::sendJsonResponse([
                'error' => false,
                'done' => true,
                'resultHTML' => bxmsksync\SyncTools::doneHTML()
            ]);
        } else {
            bxmsksync\SyncTools::sendJsonResponse([
                'error' => false,
                'done' => false,
                'resultHTML' => bxmsksync\SyncTools::progressHTML($progress)
            ]);
        }
    }

    bxmsksync\SyncTools::sendJsonResponse([
        'error' => true,
        'message' => 'Произошла ошибка при попытке получить данные'
    ]);
}
    