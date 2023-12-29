<?php
function compile_and_run($program, $input){
    if (!is_dir("Program")) {
        mkdir("Program", 0777, true);
    }
    chdir("Program");

    $file_name = uniqid('code_') . '.c';
    file_put_contents($file_name, $program);


    $output_name = uniqid('program_') . '.exe';
    $compile_result = shell_exec("gcc $file_name -o $output_name 2>&1");


    if (strpos($compile_result, 'error') !== false) {
        unlink($file_name);
        chdir("../");
        return "Compile error: $compile_result\n";
    }

    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w")   // stderr
    );

    $process = proc_open($output_name, $descriptorspec, $pipes);

    if (!is_resource($process)) {
        unlink($file_name);
        unlink($output_name);

        chdir("../");

        return "Error: Unable to open process.\n";
    }

    fwrite($pipes[0], $input);
    fclose($pipes[0]);
    $output = "";

    $status = proc_get_status($process);

    /*c言語のプログラム使用して止める方法
    //$pid = $status['pid'];

    $command = "check_process" . escapeshellarg($output_name);
    $result = exec($command, $output_temp, $return_var);

    print_r($return_var);
    print_r($output_temp);
    if($return_var == -1)echo("エラー");
    if ($return_var == 0) {
        $output = stream_get_contents($pipes[1]);
    } else {
        //exec("taskkill /IM " . escapeshellarg($output_name) . " /F");
        $output = substr($output, 0, 200) . '...';
        $output = "プログラムに無限ループを含む要素が無いか確かめてください。\n【途中までの実行結果】\n" .$output;
    }
    */
    $timeout =1;//1秒　　　　　　　(while文)やり方
    $is_done = 0;
    $start_time = microtime(true);
    while(proc_get_status($process)['running']) {
        if((microtime(true) - $start_time) >= $timeout){
            exec("taskkill /IM " . escapeshellarg($output_name) . " /F");
            proc_terminate($process);
            $output = "プログラムに無限ループまたは入力待ちになっている含む要素が無いか確かめてください。";
            $is_done = 1;
            break;
        }
    }
    if($is_done != 1) {
        $output_temp = stream_get_contents($pipes[1]);
        if (strlen($output_temp) > 200) {
            $output .= substr($output_temp, 0, 200) . '...';
        } else {
            $output .= $output_temp;
        }
    }

    /*$is_loop = false;
    $timeout = 1;//1秒　　　
    $time_count = 0;
    $start_time = microtime(true);
    while(proc_get_status($process)['running']) {
        if((microtime(true) - $start_time) >= $timeout || $time_count > 100000) {
            $is_loop = true;
            break;
        }
        echo($time_count++." ");
    }
    if($is_loop) {
        exec("taskkill /IM " . escapeshellarg($output_name) . " /F");
        $output = "プログラムに無限ループまたは入力待ちになっている含む要素が無いか確かめてください。\n【途中までの実行結果】\n";
        $output .= substr(stream_get_contents($pipes[1]), 0, 200) . '...';
        proc_terminate($process);
    }
    else {
        $output_temp = stream_get_contents($pipes[1]);
        if (strlen($output_temp) > 200) {
            $output .= substr($output_temp, 0, 200) . '...';
        } else {
            $output .= $output_temp;
        }
    }*/

    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }

    proc_close($process);

    unlink($file_name);
    $result = @unlink($output_name);

    chdir("../");

    return $output;
}
?>
