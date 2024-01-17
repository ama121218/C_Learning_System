<?php


//phpのforkを使った処理、xamppの影響でできない
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
    $is_loop = false;

    $pid = pcntl_fork();

    if ($pid == -1) {
        die('フォークに失敗しました');
    } elseif ($pid) {
        // 親プロセス
        echo "親プロセス: 子プロセス($pid)の終了を待っています\n";
        pcntl_wait($status); // 子プロセスの終了を待つ
        echo "親プロセス: 子プロセスが終了しました\n";
    } else {
        // 子プロセス
        echo "子プロセス: 処理を実行しています\n";
        $count = 0;
        while(proc_get_status($process)['running']){
            if($count > 3){
                $is_loop = true;
                break;
            }
            sleep(1);
            $count++;
        }
        echo "子プロセス: 処理が完了しました\n";
        exit(0); // 子プロセスを終了
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
    }

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
