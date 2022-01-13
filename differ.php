<?php

if (empty($argv[1]) || empty($argv[2]) || !file_exists($argv[1]) || !file_exists($argv[2])) {
    exit("Usage: php -f differ.php /path/ru_file.php /path/en_file.php\n");
}

$strings = [
    'ru' => file($argv[1]),
    'en' => file($argv[2]),
];

$results = [];

function search_en_matches($str_ru, $n)
{
    global $strings;
    $substr_before = preg_replace('#^([^А-яйёЁй]*).*#uis', '$1', $str_ru);
    // echo $substr_before . PHP_EOL;
    $substr_after = preg_replace('#.*?([^А-яйёЁй]*)$#uis', '$1', $str_ru);
    // echo $substr_after . PHP_EOL;
    $substr_ru = str_replace($substr_before, '', $str_ru);
    $substr_ru = str_replace($substr_after, '', $substr_ru);
    $substr_ru = trim($substr_ru);

    $en_options = [];
    $localizations = [];

    if (isset($strings['en'][$n]) && strpos($strings['en'][$n], $substr_before) !== false && strpos($strings['en'][$n], $substr_after) !== false) {
        $substr_en = str_replace($substr_before, '', $strings['en'][$n]);
        $substr_en = str_replace($substr_after, '', $substr_en);
        $substr_en = trim($substr_en);
        $en_options[] = $substr_en;
        $localizations[] = "$substr_ru = \"$substr_en\"";
    }

    foreach ($strings['en'] as $key => $str) {
        if (strpos($str, $substr_before) !== false && strpos($str, $substr_after) !== false) {
            $substr_en = str_replace($substr_before, '', $str);
            $substr_en = str_replace($substr_after, '', $substr_en);
            $substr_en = trim($substr_en);
            $en_options[] = $substr_en;
            $localizations[] = "$substr_ru = \"$substr_en\"";
        }
    }

    $localizations = array_unique($localizations);
    $en_options = array_unique($en_options);

    return [
        0 => trim($str_ru),
        1 => $substr_before,
        2 => $substr_after,
        3 => $substr_ru,
        4 => $en_options,
        5 => $localizations
    ];
}

foreach ($strings['ru'] as $key => $str) {
    // $str = rtrim($str);
    if (preg_match('#[А-яйёЁй]#uis', $str, $matches)) {
        $arr = search_en_matches($str, $key);
        if (!in_array($arr, $results)) {
            $results[] = $arr;
        }
    }
}

$arr = array_filter(array_column($results, 5));

$items = [];
foreach ($arr as $item) {
    if (!in_array($item, $items)) {
        $items[] = $item;
    }
}


print_r($items);
