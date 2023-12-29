<?php

function parse_tokens($tokens)
{
    $double_byte_character = array();

    $double_byte_character[] = "　";
    $double_byte_character[] = "（";
    $double_byte_character[] = "）";
    $double_byte_character[] = "｛";
    $double_byte_character[] = "｝";
    $double_byte_character[] = "＋";
    $double_byte_character[] = "ー";
    $double_byte_character[] = "＊";
    $double_byte_character[] = "／";
    $double_byte_character[] = "％";
    $double_byte_character[] = "｜";
    $double_byte_character[] = "＆";
    $double_byte_character[] = "’";
    $double_byte_character[] = "”";
    $double_byte_character[] = "＝";
    $double_byte_character[] = "＜";
    $double_byte_character[] = "＞";
    $double_byte_character[] = "！";

    require_once './search_operator.php';      // 演算子のデータベースからタイプ名を取ってくる
    require_once './searchDB.php';          // データベース接続用の関数

    global $put_error_message;

    $token_objects = array();
    $line = 1;


    class Word
    {// タイプ、字句、行番号のオブジェクトの宣言
        public $word;//字句
        public $type;//タイプ
        public $line;//行番号
        function __construct($word, $type, $line)
        {
            $this->word = $word;
            $this->type = $type;
            $this->line = $line;
        }
    }

    for ($i = 0; $i < sizeof($tokens); $i++) {
        // 文字列 OR 記号
        if (mb_ereg('\p{Alnum}', $tokens[$i]) || strpos($tokens[$i], "_")||strpos($tokens[$i], ".")) { // アルファベット文字、数字、「_」が来た時(文字列だったら）
            if(is_numeric($tokens[$i])){
                $token_objects[] = new Word($tokens[$i],"N",$line);
                continue;
            }
            $result = searchDB("SELECT * FROM another_reserved_word WHERE word = " . "'" . $tokens[$i] . "'");
            if (mysqli_num_rows($result) == 0) {
                //$token_objects[] = new Word($result, "python", $line);
            }
            else{
				while($row = mysqli_fetch_assoc($result)){
    					$rs = $row['error_string'];
				}
				put_error_message($line,"A",$rs);
                continue;
			}
            // 予約語のデータベースからタイプ名を取ってくる (行３)

            $result = searchDB("SELECT * FROM c_reserved_words WHERE word = " ."'".$tokens[$i]."'");
            if(mysqli_num_rows($result) == 0){
                $token_objects[] = new Word($tokens[$i],"id",$line);
            }
            else{
                while($row = mysqli_fetch_assoc($result)) {
                    $token_objects[] = new Word($tokens[$i], $row['word_number'], $line);
                }
            }
        }
        else if ($tokens[$i] == "\n") {
            $line++;
        } else if ($tokens[$i] == "\t") {
        } else if ($tokens[$i] == '"') { // ダブルクォーテーションが来たとき
            for ($j = $i+1; $j < sizeof($tokens); $j++) {
                if ($tokens[$j] == '"') {
                    $s = "";         // 結合のための初期化
                    for (; $i <= $j; $i++) {   // これで動かなければ$kを使う
                        $s = $s . $tokens[$i];
                    }
                    $token_objects[] = new Word($s, "S", $line);
                    $i = $j;
                    break;
                }
                if($j==sizeof($tokens)-1)put_error_message($line,"A",'"が足りません');
            }
        }else if ($tokens[$i] == "'") { // シングルクォーテーションが来たとき
            for ($j = $i+1; $j < sizeof($tokens); $j++) {
                if ($tokens[$j] == "'") {
                    $s = "";
                    for (; $i <= $j; $i++) {
                        $s = $s . $tokens[$i];
                    }
                    $token_objects[] = new Word($s, "C", $line);
                    $i = $j;
                    break;
                }
                if($j==sizeof($tokens)-1)put_error_message($line,"A","'が足りません");
            }
        } else{                 // 記号だったら
            // 演算子のデータベースからタイプ名を取ってくる (行４)
            list($word, $type) = serach_operator_type($tokens, $tokens[$i], $i);
            if($word==null){//空だったら
                foreach($double_byte_character as $str){
                    if ($tokens[$i] == $str) {
                        put_error_message($line,"A","全角文字の「{$tokens[$i]}」が含まれています");
                        $token_objects[] = new Word($tokens[$i], "Z", $line);
                        continue 2;
                    }
                }
                if($tokens[$i]=="")
                echo($line.$tokens[$i].":この文字は登録されていません"."<br>");
            }
            else {
                if ($type == "co1") {
                    for ($j = $i+1; $j < sizeof($tokens); $j++) {
                        if ($tokens[$j] == "\n" || $j == sizeof($tokens) - 1) {
                            $i = $j;
                            $line++;
                            continue 2;
                        }
                    }
                }
                $token_objects[] = new Word($word, $type, $line);
                $i += strlen($word) - 1;
            }
        }
    }
    $start_end = array();
    for ($i = 0; $i < sizeof($token_objects); $i++) {
        if ($token_objects[$i]->type == "co2") {
            for ($j = $i+1; $j < sizeof($token_objects); $j++) {

                if ($token_objects[$j]->type == "co3") {
                    array_splice($token_objects,$i, $j-$i+1);
                    $i = $i-1;
                    break;
                }
                if($j == sizeof($token_objects));//error
            }
        }
    }


    return $token_objects;

}


