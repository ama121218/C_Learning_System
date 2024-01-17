<?php

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