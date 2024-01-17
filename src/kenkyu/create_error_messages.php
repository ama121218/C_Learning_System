<?php

function create_error_messages($error_messages)
{

    $error_messages_output = "";

    foreach ($error_messages as $s) {
        if ($s->type == "A") {//エラーメッセージタイプAの時
            $error_messages_output .= ($s->line . "行目 " . $s->message . "<br>");//行番号とエラーそのまま
        } else if ($s->type == "B") {//タイプBの時
            $str_sql = "SELECT * FROM c_reserved_words WHERE word_number = " . $s->number;//予約語番号検索
            $rs = searchDB($str_sql);
            $result = '';
            while ($row = mysqli_fetch_assoc($rs)) {
                $result = $row['word'];
            }

            $str_sql = "SELECT * FROM c_grammar_error WHERE number = " . $s->number;//予約語番号から正規の文法データベース検索
            $rs = searchDB($str_sql);
            $ss = $result . ' の書き方は<br><br>';
            $cnt = 0;
            while ($row = mysqli_fetch_assoc($rs)) {
                if ($cnt >= 1) {
                    $ss = $ss . "もしくは<br><br>";//二つ以上の場合
                }
                $ss = $ss . $row['grammar'] . "<br><br>";
                $cnt++;
            }
            $ss = str_replace('\\n', '<br>', $ss);//正規の文法の中に改行があった時
            $ss = str_replace('\\t', '　　　　', $ss);//正規の文法の中に水平タブがあった時
            $error_messages_output .= ($s->line . "行目 ");
            $error_messages_output .= ("<div style=\"color: blue; display: inline-block; _display: inline;\" class=\"mouse\">" . $result . "<span class=\"word\">" . $ss . "</span></div>");
            $error_messages_output .= ("<span class=\"posi\">&nbsp;の文法が間違っています</span><br>");
        }
    }
    return $error_messages_output;
}

?>