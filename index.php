<?php

function highlightKeywords($text, $array_of_words) {
    $delimiters = array(" ", "\n", "\t", ".", ",", "!", "?", "-", ":", ";", "[", "]","(", ")", "{", "}");
    $array_of_words_lower = array_map(function($v){return mb_strtolower($v, 'UTF-8');}, $array_of_words);
    $results = array();
    $textE =  $text;
    foreach ($array_of_words_lower as $key => $word_lower) {
        $do_search = true;
        $cut_count = 0;
        $text = $textE;
        while ($do_search) {
            $position = mb_stripos($text, $word_lower, true);
            if ($position) {
                $before = mb_substr($text, $position-1, 1);
                $after = mb_substr($text, $position + mb_strlen($word_lower), 1);
                if (in_array($before, $delimiters) && in_array($after, $delimiters)) {
                    $results[$key]['position'] = $position + $cut_count;
                    $results[$key]['length'] = mb_strlen($word_lower);
                    $do_search = false;
                    $text = mb_substr($text, $position + mb_strlen($word_lower), mb_strlen($text) - ($position + mb_strlen($word_lower)));
                    $cut_count = $position + mb_strlen($word_lower);
                } else {
                    $text = mb_substr($text, $position + mb_strlen($word_lower), mb_strlen($text) - ($position + mb_strlen($word_lower)));
                    $cut_count = $position + mb_strlen($word_lower);
                }
            } else {
                $do_search = false;
            }
        }
    }

    usort($results, function($a, $b){
        return $a["position"] > $b["position"];
    });

    $output= '';
    $cut_count = 0;
    $text = $textE;
    foreach ($results as $key => $result) {
        $left = mb_substr($text, 0, $results[$key]['position'] - $cut_count);
        $word = mb_substr($text, $results[$key]['position']  - $cut_count, $results[$key]['length']);
        $right = mb_substr($text, $results[$key]['position']  - $cut_count + mb_strlen($word), 1);
        $output.= $left . "{{" . $word . "}}" . $right;
        $text = mb_substr($text, $results[$key]['position'] + $results[$key]['length'] +1  - $cut_count, mb_strlen($text));
        $cut_count = $results[$key]['position'] + $results[$key]['length'] + 1;
    }
    $output.= $text;

    return $output;
}

$array_of_words = array('php', 'xml', 'ООП', 'интерфейс', 'Zend');
$text = 'Пятая версия PHP была выпущена разработчиками 13 июля 2004 года. Изменения включают обновление ядра Zend (Zend Engine 2), что существенно увеличило эффективность интерпретатора. Введена поддержка языка разметки XML. Полностью переработаны функции ООП, которые стали во многом схожи с моделью, используемой в Java. В частности, введён деструктор, открытые, закрытые и защищённые члены и методы, окончательные члены и методы, интерфейсы и клонирование объектов. Нововведения, однако, были сделаны с расчётом сохранить наибольшую совместимость с кодом на предыдущих версиях языка. На данный момент последней стабильной веткой является PHP 5.3, которая содержит ряд изменений и дополнений';

$result = highlightKeywords($text, $array_of_words);

echo "<h3>Исходные данные</h3><h4>Слова</h4><p>". implode(', ', $array_of_words) ."</p><h4>Текст</h4><p>$text</p>";
echo "<h3>Результат</h3><p>$result</p>";


/*
SELECT user_id, nickname, DAYOFMONTH(birthday) day_of_birth, MONTH(birthday) month_of_birth,
YEAR(CURDATE())- YEAR(birthday) age, DAY(birthday) - DAY(CURDATE()) `interval`
FROM `users`
WHERE DAYOFYEAR(birthday) > DAYOFYEAR(CURDATE())
ORDER BY DAYOFYEAR(birthday) ASC
LIMIT 5

Оптимизация времени выполнения запроса:   

CREATE TABLE `users` (
`user_id` int(11) NOT NULL default '0',
`birthday` date NOT NULL default '0000-00-00',
`nickname` char(32) NOT NULL default '',
`password` char(32) NOT NULL default '',
`day_of_year` int(11) NOT NULL ,
PRIMARY KEY (`user_id`),
INDEX idx_day_of_year (day_of_year)

SELECT user_id, nickname, DAYOFMONTH(birthday) day_of_birth, MONTH(birthday) month_of_birth,
YEAR(CURDATE())- YEAR(birthday) age, DAY(birthday) - DAY(CURDATE()) `interval`
FROM `users`
WHERE day_of_year > DAYOFYEAR(CURDATE())
ORDER BY day_of_year ASC
LIMIT 5
 */