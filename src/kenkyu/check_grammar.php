<?php

function checkGrammar($token_parenthese_objects){//文法チェック
    require_once './searchDB.php';
    for($i=0;$i<sizeof($token_parenthese_objects);$i++){//字句オブジェクト及び括弧内オブジェクト配列の読み込み
        if(is_numeric($token_parenthese_objects[$i]->type)){//もしタイプが数字だったら

            $line = $token_parenthese_objects[$i]->line;//行番号
            $grammer_number = $token_parenthese_objects[$i]->type;//予約語番号
            $end;//終わりのindexの先定義
            $is_grramar_correct = false;//文法があっているかいないかのboolean変数
            $str_sql = "SELECT * FROM c_grammar WHERE program_number = " . $token_parenthese_objects[$i]->type;//sql文
            $rs = searchDB($str_sql);//予約語から文法の検索
            if(mysqli_num_rows($rs)==0){//行の数が0だったらcontinue//空だった時の処理
                continue;
            }

            while($row = mysqli_fetch_assoc($rs)){//文法をひとつずつ読み込み//ex:if(){} or if();

                //echo($row['string_data']." ");


                $grammars = preg_split("/-/",$row['string_data']);//split関数で「-」を区切りに配列に保存
                $cnt = 0;//どこまで読み込んだかを記録する変数
                for($j=0;$j<sizeof($grammars);$j++){//文法配列の読み込み

                    //echo("<br>".$grammars[$j]." = ".$token_parenthese_objects[$i+$j+$cnt]->type."<br>");

                    if($grammars[$j]=="D"){//もしDだったらセミコロンまで読み込み
                        $k_cnt=0;//次のfor文でどれだけ読み込んだかを記録するための変数
                        //字句オブジェクト及び括弧内オブジェクト配列の一部読み込み
                        for($k=$i+$j+$cnt;isset($token_parenthese_objects[$k])&&$k<sizeof($token_parenthese_objects);$k++){
                            if($token_parenthese_objects[$k]->type==";"){//セミコロンまで
                                $cnt = $cnt + $k_cnt-1;
                                continue 2;
                            }
                            $k_cnt++;
                        }
                    }
                    if($grammars[$j]==";"){//文法データベースのセミコロンがきたら「;」「,」のどちらでも良い処理
                        if(!isset($token_parenthese_objects[$i+$j+$cnt])||($token_parenthese_objects[$i+$j+$cnt]->type!=";"&&$token_parenthese_objects[$i+$j+$cnt]->type!=","&&$token_parenthese_objects[$i+$j+$cnt]->type!=")")){
                            continue 2;
                        }
                    }else if($grammars[$j]=="ag"){
                        if(!isset($token_parenthese_objects[$i+$j+$cnt])||substr($token_parenthese_objects[$i+$j+$cnt]->type, 0, 2) == "ag"){
                            continue 2;
                        }
                    }
                    else{//上記の条件以外だったら普通に正誤チェック//違ったらもうひとつの文法も見る
                        if(!isset($token_parenthese_objects[$i+$j+$cnt])||$grammars[$j]!=$token_parenthese_objects[$i+$j+$cnt]->type){
                            continue 2;
                        }
                    }
                    if($grammars[$j]=="id"){//変数のidだったら配列の[]を無視
                        $cnt = checkK($token_parenthese_objects,$i+$j,$cnt);
                    }
                    if($j==sizeof($grammars)-1){//最後までチェックして大丈夫そうだったら文法成立
                        $is_grramar_correct = true;//trueの代入
                        $end = $j+$i;//終わりのindex保存
                        break 2;
                    }
                }
            }
            if(!$is_grramar_correct){//文法が当てはまらなかったら予約語を青くするためにエラーメッセージタイプBを送る
                put_error_message($line,"B",$token_parenthese_objects[$i]->type);
                //echo("<br>文法不成立".$token_parenthese_objects[$i]->type."<br>");
            }else{
                //echo($grammer_number."文法成立");
                for($j=$i;$j<$end;$j++){
                    //文法が成立したらTGKのアウトタイプに予約語番号を入れる
                    if($token_parenthese_objects[$j]->type=="G"||$token_parenthese_objects[$j]->type=="T"){
                        $token_parenthese_objects[$j]->outtype = $grammer_number;
                    }
                }
            }
        }

    }
    foreach($token_parenthese_objects as $s){//括弧の中身も見る処理
        if($s->type=="G"||$s->type=="T"||$s->type=="K"){
            checkGrammar($s->token_objects);
        }
    }
}

?>