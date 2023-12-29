<?php
function groupBracketContent($token_objects){

    $token_parenthese_objects = array();//配列の作成
    for($i=0;$i<sizeof($token_objects);$i++){//字句解析の結果を最初から最後まで参照
        $size = sizeof($token_parenthese_objects);//構造の配列サイズを保存
        $token_parenthese_objects[] = new sentence($token_objects[$i]->type,$token_objects[$i]->word,$token_objects[$i]->line);
        $line = $token_objects[$i]->line;//行番号を保存
        if($token_objects[$i]->type=="{"){//{がある時
            if($i==sizeof($token_objects)-1){
                put_error_message($line,"A","}が足りません");
                break;
            }
            if($token_objects[$i+1]->type=="}"){//}が見つかった時({}内が空だった時)
                $size = sizeof($token_parenthese_objects);//構造の配列サイズを保存
                $token_parenthese_objects[$size] = new sentence("G","",$line);//構造の配列にセンテンスオブジェクトのインスタンスを作成
                $token_parenthese_objects[$size]->token_objects = array();//startからendまでを切り取った配列の宣言
                continue;
            }
            $cnt = 1;//{の数を数える変数
            for($j=$i+1;$j<sizeof($token_objects);$j++){
                if($token_objects[$j]->type=="{"){
                    $cnt++;
                }else if($token_objects[$j]->type=="}"){
                    $cnt--;
                }
                if($cnt==0){//{の数だけ}があった時
                    $size = sizeof($token_parenthese_objects);//構造の配列サイズを保存
                    $token_parenthese_objects[] = new sentence("G","",$line);//構造の配列にセンテンスオブジェクトのインスタンスを作成
                    $token_parenthese_objects[$size]->token_objects = groupBracketContent(array_slice($token_objects,$i+1,$j-$i-1));
                    //始めと終わりを設定した字句解析の配列をこの関数にもう一度送る
                    $i = $j-1;
                    break;
                }
                if($j==sizeof($token_objects)-1){//エラーメッセージに加える
                    put_error_message($line,"A","}が足りません");
                }
            }

        }
        else if($token_objects[$i]->type=="("&&$token_objects[$i+1]->type!=")"){//{}と同様
            if(!isset($token_objects[$i+1])){//次がプログラムの最後でだったら
                put_error_message($line,"A",")が足りません");//エラーメッセージに加える
                continue;
            }
            if($token_objects[$i+1]->type==")"){//}が見つかった時({}内が空だった時)
                $size = sizeof($token_parenthese_objects);//構造の配列サイズを保存
                $token_parenthese_objects[$size] = new sentence("T","",$line);//構造の配列にセンテンスオブジェクトのインスタンスを作成
                $token_parenthese_objects[$size]->token_objects = array();//startからendまでを切り取った配列の宣言
                continue;
            }
            $cnt = 1;
            for($j=$i+1;$j<sizeof($token_objects);$j++){
                if($token_objects[$j]->type=="("){
                    $cnt++;
                }else if($token_objects[$j]->type==")"){
                    $cnt--;
                }
                if($cnt==0){
                    $size = sizeof($token_parenthese_objects);
                    $token_parenthese_objects[$size] = new sentence("T","",$line);
                    $token_parenthese_objects[$size]->token_objects = groupBracketContent(array_slice($token_objects,$i+1,$j-$i-1));
                    $i = $j-1;
                    break;
                }
                if($j==sizeof($token_objects)-1){
                    put_error_message($line,"A",")が足りません");
                }
            }
            if($i==sizeof($token_objects)-1){//次がプログラムの最後でだったら
                put_error_message($line,"A","}が足りません");//エラーメッセージに加える
                continue;
            }
        }
        else if($token_objects[$i]->type=="["&&$token_objects[$i+1]->type!="]"){//{}と同様
            $cnt = 1;
            for($j=$i+1;$j<sizeof($token_objects);$j++){
                if($token_objects[$j]->type=="["){
                    $cnt++;
                }else if($token_objects[$j]->type=="]"){
                    $cnt--;
                }
                if($cnt==0){
                    $size = sizeof($token_parenthese_objects);
                    $token_parenthese_objects[$size] = new sentence("K","","$line");
                    $token_parenthese_objects[$size]->token_objects = groupBracketContent(array_slice($token_objects,$i+1,$j-$i-1));
                    $i = $j-1;
                    break;
                }
                if($j==sizeof($token_objects)-1){
                    put_error_message($line,"A","]が足りません");
                }
            }
        }else if(strlen($token_objects[$i]->type) >= 3 && substr($token_objects[$i]->type, 0, 2) == "ag"){//代入処理--未完成--無視
            for($j=$i+1;$j<sizeof($token_objects);$j++){
                if($token_objects[$j]->type==";"){
                    if($i+1>$j-1){
                        put_error_message($line,"A","代入するものが空です");
                        break;
                    }
                    $size = sizeof($token_parenthese_objects);
                    $token_parenthese_objects[$size] = new sentence("Q","",$line);
                    $token_parenthese_objects[$size]->token_objects = groupBracketContent(array_slice($token_objects,$i+1,$j-$i-1));
                    $i = $j-1;
                    break;
                }
                else if(!(is_numeric($token_objects[$j]->type))&&
                    $token_objects[$j]->type!="N"&&
                    $token_objects[$j]->type!="id"&&
                    $token_objects[$j]->type!="S"&&
                    $token_objects[$j]->type!="("&&
                    $token_objects[$j]->type!=")"&&
                    $token_objects[$j]->type!="M"&&
                    $token_objects[$j]->type!="A")
                {

                    put_error_message($line,"A","代入できないものがあります");
                    break;
                }
            }
        }
    }
    return $token_parenthese_objects;
}

class sentence{
    public $type; //括弧だったらGTK//違ったら字句タイプ
    public $word;
    public $token_objects;//字句オブジェクトの配列
    public $outtype;//括弧内オブジェクトの予約語番号
    public $line;
    function __construct($type,$word,$line)
    {
        $this->type = $type;
        $this->word = $word;
        $this->line = $line;
    }

}


?>