<?php

function joinK($token_parenthese_objects){

    $cnt=0;
    for($i=0;$i<sizeof($token_parenthese_objects)-$cnt;$i++){
        if($token_parenthese_objects[$i]->type=="id"){
            $cnt = checkK($token_parenthese_objects,$i,0);
            $token_parenthese_objects[$i]->token_objects = array_slice($token_parenthese_objects,$i+1,$cnt);
            array_splice($token_parenthese_objects, $i+1, $cnt);
        }
    }
    return $token_parenthese_objects;
}
function checkK(&$token_parenthese_objects,$i,$cnt){
    if(isset($token_parenthese_objects[$i+1])&&($token_parenthese_objects[$i+1]->type=="[")){
        if(isset($token_parenthese_objects[$i+2])&&($token_parenthese_objects[$i+2]->type=="K")){
            if(isset($token_parenthese_objects[$i+3])&&($token_parenthese_objects[$i+3]->type=="]")){
                if(isset($token_parenthese_objects[$i+4])&&($token_parenthese_objects[$i+4]->type=="[")){
                    return checkK($token_parenthese_objects,$i+3,$cnt+3);
                }else{
                    return $cnt+3;
                }
            }
        }
    }
    return $cnt;
}


?>