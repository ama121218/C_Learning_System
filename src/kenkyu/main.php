<?php
//中身がない参照を防ぐ
$program1 = "";//空のストリング代入
$input = "";
$compileError_executionResult = "";
$token_objects = array();//空の配列を代入
$error_messages = array();//空の配列を代入
$include_list = array();//空の配列を代入
$print_error_messages = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["program1"])) {//入力フォームが空ではない時
        $program1 = $_POST["program1"];//入力されたプログラムを保存
        $input = $_POST["input"];
        require_once './compile_and_run.php';//コンパイルプログラムファイルの読み込み
        $compileError_executionResult = compile_and_run($program1, $input);
        if (strpos($compileError_executionResult, "Compile error:") !== false) {

            require_once './parse_tokens.php';
            require_once './split_tokens.php';
            require_once './group_bracket_content.php';
            require_once './check_grammar.php';
            require_once './join_k.php';
            require_once './checkT.php';
            require_once './create_error_messages.php';

            $tokens = split_tokens($program1);//字句分割
            $token_objects = parse_tokens($tokens);//字句解析
            $token_parenthese_objects = groupBracketContent($token_objects);//括弧内オブジェクトの作成
            $token_parenthese_objects = joinK($token_parenthese_objects);//配列の[]をIDの中に結合
            checkGrammar($token_parenthese_objects);//文法確認

            //字句解析の確認
            /*function echoObjects($token_parenthese_objects)
            {
                foreach ($token_parenthese_objects as $token_parentheses_object) {
                    echo(" " . $token_parentheses_object->type . " ");
                    if ($token_parentheses_object->type == "T") {
                        echo("(");
                        echoObjects($token_parentheses_object->token_objects);
                        echo(")");
                    } else if ($token_parentheses_object->type == "G") {
                        echo("{");
                        echoObjects($token_parentheses_object->token_objects);
                        echo("}");
                    } else if ($token_parentheses_object->type == "K") {
                        echo("[");
                        echoObjects($token_parentheses_object->token_objects);
                        echo("]");
                    }
                }
            }*/
            //echoObjects($token_parenthese_objects);

            checkT($token_parenthese_objects);
            $print_error_messages = create_error_messages($error_messages);
        }
    }
}


function put_error_message($line,$type,$content){//エラーメッセージを配列に追加する案数
    global $error_messages;//エラーメッセージ配列変数のグローバル呼び出し
    $size = sizeof($error_messages);//配列サイズ
    $error_messages[$size] = new error_();//エラーオブジェクトのインスタンス
    $error_messages[$size]->line = $line;//行番号
    $error_messages[$size]->type = $type;//タイプ
    if($type=="B"){//もしエラーメッセージがタイプBったら予約語番号を代入
        $error_messages[$size]->number = $content;
    }else{//Aだったらそのまま代入
        $error_messages[$size]->message = $content;
    }
}
class error_{//エラーオブジェクト
    public $line;//行番号
    public $type;//タイプ
    public $message;//メッセージ
    public $number;//タイプBだった時の予約語番号
}

?>

<!--//////////////////////HTML/////////////////////////--!>
<DOCTYPE HTML>
    <!--//////CSS/////////--!>
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <html lang="ja">
    <HEAD>
        <META charset="UTF-8" />
        <META name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>C言語の学習支援システム</title>

    </HEAD>

    <BODY>

    <H2>C言語の学習支援システム</H2>
     プログラムを入力し、確認ボタンを押してください。
    <hr>
    <form name = "sotuken" action="?" method="post">


        <b>プログラム</b><br>
        <table class="table1">
            <tbody>
            <tr>
                <td>
                    <textarea name="program1" class="txt1" id="program1"><?php print"$program1"; ?></textarea>
                </td>
                <td>
                    <?php
                        print $print_error_messages;
                    ?>
                </td>
            </tr>
            </tbody>
        </table>

        <input type="submit" value="確認">　
        <input type="reset" value="クリア" onclick = "clearprogram()">


    <div class = "tab">
        <br>
	<button type="button" class="tablinks active" onclick="openTab(event, 'Table2')">実行結果</button>
  	<button type="button" class="tablinks" onclick="openTab(event, 'Table3')">入力</button>


    <div id="Table2" class="tabcontent active">
    <table class="table2"  bgcolor="#fff8dc">
        <tbody>
        <tr>
            <td>
                <?php
                    echo $compileError_executionResult;
                ?>
            </td>
        </tr>
        </tbody>
    </table>
    </div>

    <div id="Table3" class="tabcontent">
    <table class="table3"  bgcolor="#e6ffe9">
       <tbody>
        <tr>
            <td>
                <textarea id="input" name="input" rows="4" cols="50"><?php print"$input"; ?></textarea>
            </td>
        </tr>
        </tbody>
    </table>
    </div>
    </div>
</form>

    </BODY>
    <!--行番号追加のためのjqueryインポートと行番号追加プラグイン--!>
    <script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="js/bcralnit.js"></script>
    <script src="js/script.js"></script>

    </HTML>