<?php
function split_tokens($program){
    $program = str_replace("\r","",$program);

    $tokens = array();
    $program = iconv(mb_detect_encoding($program), "UTF-8", $program);

    $length = mb_strlen($program, "UTF-8");
    for($i=0;$i<$length;$i++){
        $word = "";
        for($j=$i;$j<$length;$j++,$i++){
            $char = mb_substr($program, $i, 1);
            if(mb_ereg('\p{Alnum}', $char)||$char=="_"||$char=="."){
                $word = $word.$char;
                if($j==$length-1)if($word!="")$tokens[] = $word;
            }else if($char=="\t"||$char=="\n"){
                if($word!="")$tokens[] = $word;
                $tokens[] = $char;
                break;
            }else if($char==" "){
                if($word!="")$tokens[] = $word;
                break;
            }
            else{
                if($word!="")$tokens[] = $word;
                $tokens[] = $char;
                break;
            }
        }
    }
    return $tokens;
}
?>