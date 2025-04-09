<?php


require_once __DIR__ . '/core/init.php';


// try {
//     $blackoutDefinition = new BlackoutDefinition('test', '2021-01-01', '2021-01-02');
//     BlackoutDefinitionsTable::getInstance()->addOrSetBlackoutDefinition($blackoutDefinition);
//     echo "blackout_definitionの追加に成功しました。\n";
// } catch (Exception $e) {
//     echo "エラー: blackout_definitionの追加に失敗しました: " . $e->getMessage() . "。\n";
// }


try {
    $blackoutDefinitions = BlackoutDefinitionTable::getInstance();
    // $blackoutDefinitions->deleteRecordByID(3);
    // $blackoutDefinitions->deleteRecordByID(4);
    // $blackoutDefinitions->deleteRecordByID(5);
    $blackoutDefinitions->deleteRecordByID(6);
    echo "blackout_definitionの削除に成功しました。\n";
} catch (Exception $e) {
    echo "エラー: blackout_definitionの削除に失敗しました: " . $e->getMessage() . "。\n";
}
