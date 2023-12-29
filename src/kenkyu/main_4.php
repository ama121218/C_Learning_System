<?php

if(isset($_POST["program1"])) {//入力フォームが空ではない時
    $program1 = $_POST["program1"];//入力されたプログラムを保存
    $input = $_POST["input"];
    $error_messages = array();//空の配列を代入
    require_once './compile_and_run_p.php';
    $compileError_executionResult = compile_and_run($program1,$input);
    if(strpos($compileError_executionResult, "Compile error:")!==false) {

        require_once './parse_tokens.php';
        require_once './split_tokens.php';
        require_once './group_bracket_content.php';
        require_once './check_grammar.php';
        require_once './join_k.php';
            top: 0;
        $tokens = split_tokens($program1);
        $token_objects = parse_tokens($tokens);
        $token_parenthese_objects = groupBracketContent($token_objects);
        $token_parenthese_objects = joinK($token_parenthese_objects);
        /*
        print_r($tokens);
        echo("<br><br>");
        print_r($token_objects);
        echo("<br><br>");
        */
        checkGrammar($token_parenthese_objects);


        function echoObjects($token_parenthese_objects)
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
        }

        //echoObjects($token_parenthese_objects);
        checkT($token_parenthese_objects);
    }
}
else{//入力フォームが空の時、中身がない参照を防ぐ
    $program1 = "";//空のストリング代入
    $input = "";
    $compileError_executionResult = "";
    $token_objects = array();//空の配列を代入
    $error_messages = array();//空の配列を代入
    $lan1 = 0;
    $lan2 = 0;
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

function checkT($token_parenthese_objects){//if文の()の中身やfor文の()の中身を見る処理//未完成
    foreach($token_parenthese_objects as $s){
        if($s->type=="G"){
            checkT($s->token_objects);
        }
        if($s->type=="T"){
            if($s->outtype!=null){
                if($s->outtype==15){//for文
                    $cnt = 0;
                    foreach($s->token_objects as $token_object){
                        if($token_object->type==";"){
                            $cnt++;
                        }
                    }
                    if($cnt!=2){
                        put_error_message($s->line,"B",$s->outtype);
                        continue;
                    }

                }
            }
        }
    }
}

?>

<!--//////////////////////HTML/////////////////////////--!>
<DOCTYPE HTML>
    <!--//////CSS/////////--!>
    <style>
        .txt1{
            font-family:'ＭＳ明朝','細明朝体';
            font-size:15px;
            width:100%;
            height:100%;
            max-width:500px;
            max-height:250px;
            resize: none;
            position: absolute;
            left:0;
            max-heigth: 100%;
            background: #ffe6ee;
            color: #000;
            padding: 5px;
            line-height: 22px;
            border: 2px solid #696969;
            display: inline-block;
        }
        //↑プログラム入力エリア
        //max-width:600px;
        //max-height:500px;

        /////////table///////////
        .table1 {
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table1 th,.table1 td {
            border: 1px solid #CCCCCC;
            text-align: left;
            width: 500px;
            height: 250px;
            text-align:left;
            vertical-align:top;
            font-size:16px;
            padding:3px 3px;
        }
        //width: 600px;

        /////////文法確認/////////
        //青文字の予約語にマウスを乗せたときの処理
          .mouse {
              position:relative;
          }
        .mouse:hover .word {
            display: inline;
        }
        .word {
            z-index: 2;
            position:absolute;
            display: none;
            padding: 2px;
            color: black;
            border-radius: 5px;
            background:#d8dfe6;
            font-size: 12px;
            font-family:'ＭＳ明朝','細明朝体';
            text-align:left;
            vertical-align:top;
            margin-top : 12px;
            width:200px;
        }
        .posi {
            position: absolute;
        }
        /////////table2(実行結果の表示)///////////
        .table2 {
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table2 th,.table2 td {
            border: 1px solid #AAAAAA;
            text-align: left;
            width: 1200px;
            height: 100px;
            text-align:left;
            vertical-align:top;
            font-size:16px;
            padding:3px 3px;
            word-wrap: break-word;
            max-width: 1000px;
        }
	/////////table3(入力画面の表示)///////////
        .table3 {
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table3 th,.table3 td {
            border: 1px solid #AAAAAA;
            text-align: left;
            width: 1200px;
            height: 200px;
            text-align:left;
            vertical-align:top;
            font-size:16px;
            padding:3px 3px;
        }
	
	/////////////////タブ/////////////////////
	.tab {
  		overflow: hidden;
	}

	.tab button {
	  background-color: #f2f2f2;
	  border: none;
  	  outline: none;
  	  cursor: pointer;
  	  padding: 8px 16px;
  	  transition: background-color 0.3s;
	 }

	.tab button:hover {
	  background-color: #ddd;
	}

	.tab button.active {
      	  background-color: #ccc;
        }

	.tabcontent {
  	  display: none;
  	  padding: 16px;
	}


    </style>

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


                    foreach($error_messages as $s){
                        if($s->type=="A"){//エラーメッセージタイプAの時
                            print($s->line."行目 ".$s->message."<br>");//行番号とエラーそのまま
                        }else if($s->type=="B"){//タイプBの時
                            $str_sql = "SELECT * FROM c_reserved_words WHERE word_number = " . $s->number;//予約語番号検索
                            $rs = searchDB($str_sql);
                            $result = '';
                            while($row = mysqli_fetch_assoc($rs)){
                                $result = $row['word'];
                            }


                            $str_sql = "SELECT * FROM c_grammar_error WHERE number = " . $s->number;//予約語番号から正規の文法データベース検索
                            $rs = searchDB($str_sql);
                            $ss = $result.' の書き方は<br><br>';
                            $cnt=0;
                            while($row = mysqli_fetch_assoc($rs)){
                                if($cnt>=1){
                                    $ss = $ss."もしくは<br><br>";//二つ以上の場合
                                }
                                $ss = $ss.$row['grammar']."<br><br>";
                                $cnt++;
                            }
                            $ss = str_replace('\\n', '<br>', $ss);//正規の文法の中に改行があった時
                            $ss = str_replace('\\t', '　　　　', $ss);//正規の文法の中に水平タブがあった時
                            print($s->line."行目 ");
                            print("<div style=\"color: blue; display: inline-block; _display: inline;\" class=\"mouse\">".$result."<span class=\"word\">".$ss."</span></div>");
                            print("<span class=\"posi\">&nbsp;の文法が間違っています</span><br>");
                        }
                    }
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
    <script>

        $(".txt1").bcralnit({
            width: '34px',
            background: '#e0ffff',
            color: '#cc52cc'
        });
	
	
       ///////////////タブの切り替え機能/////////////////
	window.onload = function() {
 	       //実行結果表示タブが最初に選択されているようにする
 		var defaultTab = document.querySelector(".tablinks.active");
  		var defaultTabContent = document.querySelector(".tabcontent.active");
 		 if (defaultTab && defaultTabContent) {
    			defaultTabContent.style.display = "block";
 		 }
	}
	function openTab(evt, tabName) {
	//evt.preventDefault(); // フォーム送信を防止
 		 var i, tabcontent, tablinks;

 		 tabcontent = document.getElementsByClassName("tabcontent");
		 for (i = 0; i < tabcontent.length; i++) {
   		 	tabcontent[i].style.display = "none";
  		}

  		tablinks = document.getElementsByClassName("tablinks");
  		for (i = 0; i < tablinks.length; i++) {
   	 		tablinks[i].className = tablinks[i].className.replace(" active", "");
  		}		

 		 document.getElementById(tabName).style.display = "block";
  		evt.currentTarget.className += " active";
	}

	//////////Tabが押されたときの処理////////////////////////////
        function OnTabKey( e, obj ){
            if( e.keyCode!=9 ){ return; }
            e.preventDefault();

            var cursorPosition = obj.selectionStart;
            var cursorLeft     = obj.value.substr( 0, cursorPosition );
            var cursorRight    = obj.value.substr( cursorPosition, obj.value.length );

            obj.value = cursorLeft+"\t"+cursorRight;

            obj.selectionEnd = cursorPosition+1;
        }

        document.getElementById( "program1" ).onkeydown = function( e ){ OnTabKey( e, this ); }




    </script>

    </HTML>