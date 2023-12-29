<?php


function serach_operator_type(&$tokens,$word,$start){  // 演算子のデータベースでタイプ名を取ってくる
    require_once './searchDB.php';
    $lan1 = 1; // C言語
    if($lan1==1){
        $tableName = "c_operator";
    }
    //echo($word."<br>");
    $search_word = $word."%";
    //演算子の字句の文字数が多い順にソート及び演算子の検索
    $str_sql = "SELECT * FROM ". $tableName . " WHERE name LIKE "."'".$search_word."'"." ORDER BY " .$tableName.".name DESC";

    $rs = searchDB($str_sql);    //検索の結果(該当する演算子)を代入
    $result = array();
    if(mysqli_num_rows($rs)==0){ // 演算子が無いとき
        return array (null,null);
    }else if(mysqli_num_rows($rs)==1){ // 演算子が１つのとき
        $row = mysqli_fetch_assoc($rs); // 検索の結果を格納する連想配列を作成する
        return array($row['name'],$row['type']);
    }else{// 演算子が複数あるとき
        while($row = mysqli_fetch_assoc($rs)){//複数の演算子の結果を読み込み//ex:+=,++,+
            $name = $row['name'];
            $count=0; //文字数
            $b = false;
            for($i=$start;$i<strlen($name)+$start&&$i<sizeof($tokens);$i++,$count++){//演算子が来たところから後ろを照合
                if($name[$count]!=$tokens[$i]){
                    break;
                }
                if($count==strlen($name)-1){//最後までいったら
                    $b = true;//true
                }
            }
            if($b){
                return array ($name,$row['type']);
            }
        }
    }
    return array (null,null);
}
?>


