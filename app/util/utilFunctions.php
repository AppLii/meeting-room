<?php


/**
 * 連想配列のデータをHTMLテーブルとして出力します。
 *
 * @param array $pdo_assoc テーブルとして表示するPDOの結果セット
 * @param array|null $options 表示オプション（例：'class' => 'table table-striped'）
 * @throws Exception データの出力に失敗した場合
 */
function echoDataAsTable(array $pdo_assoc, ?array $options = null): void
{
    try {
        if (empty($pdo_assoc)) {
            echo '<p class="no-data">No data available</p>';
            return;
        }

        // 最初の要素が連想配列であることを確認
        if (!is_array($pdo_assoc[0])) {
            throw new Exception("Invalid data format: expected associative array");
        }

        // テーブル属性の設定
        $tableAttr = '';
        if (isset($options['class'])) {
            $tableAttr .= ' class="' . htmlspecialchars($options['class']) . '"';
        }
        if (isset($options['id'])) {
            $tableAttr .= ' id="' . htmlspecialchars($options['id']) . '"';
        }

        echo '<table border="1"' . $tableAttr . '>' . PHP_EOL;
        echo '<thead>' . PHP_EOL;
        echo '<tr>' . PHP_EOL;

        foreach ($pdo_assoc[0] as $key => $value) {
            echo "\t<th>" . htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . "</th>" . PHP_EOL;
        }

        echo '</tr>' . PHP_EOL;
        echo '</thead>' . PHP_EOL;
        echo '<tbody>' . PHP_EOL;

        foreach ($pdo_assoc as $row) {
            echo '<tr>' . PHP_EOL;
            foreach ($row as $value) {
                // nullや配列など、適切に表示できない値を処理
                if ($value === null) {
                    $displayValue = '';
                } elseif (is_array($value) || is_object($value)) {
                    $displayValue = json_encode($value, JSON_UNESCAPED_UNICODE);
                } else {
                    $displayValue = (string)$value;
                }
                echo "\t<td>" . htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8') . "</td>" . PHP_EOL;
            }
            echo '</tr>' . PHP_EOL;
        }

        echo '</tbody>' . PHP_EOL;
        echo '</table>' . PHP_EOL;
    } catch (Exception $e) {
        error_log(sprintf(
            "[util] Error displaying data table: %s",
            $e->getMessage()
        ));
        echo '<p class="error">Error displaying data: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    }
}

/**
 * 安全なJSONエンコードを行います。
 * 
 * @param mixed $data エンコードするデータ
 * @param bool $prettyPrint 整形するかどうか（デフォルト: false）
 * @return string JSONエンコードされた文字列
 * @throws Exception エンコードに失敗した場合
 */
function safeJsonEncode($data, bool $prettyPrint = false): string
{
    try {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($data, $options);
        if ($json === false) {
            throw new Exception("JSON encoding failed: " . json_last_error_msg());
        }

        return $json;
    } catch (Exception $e) {
        error_log(sprintf(
            "[util] JSON encoding error: %s\nData: %s",
            $e->getMessage(),
            var_export($data, true)
        ));
        throw new Exception("Failed to encode data to JSON: " . $e->getMessage());
    }
}

/**
 * 文字列の値を安全に表示します（XSS対策）
 * 
 * @param string|null $value 表示する値
 * @param string $default 値がnullの場合に表示するデフォルト値
 * @return string エスケープされた文字列
 */
function safeEcho(?string $value, string $default = ''): string
{
    if ($value === null) {
        return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
