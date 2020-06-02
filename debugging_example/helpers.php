<?php

function qs_passthru($command) {
    $wrappedCommand = sprintf('bash %s/trap.sh %s %s %s %s', __DIR__, 
    escapeshellarg($_POST['wf_type'].':'.$_POST['stage'].':'.$_POST['trace_id']), sprintf('%s/logs/quicksilver.log', $_ENV['HOME']), escapeshellarg($_POST['qs_description']), $command);
    
    passthru($wrappedCommand, $return_var);
    
    if (0 === $exit) {
        echo("$name completed successfully\n");
    } else {
        @trigger_error(sprintf('Command "%s" exit status: %s', $cmd, $exit), E_USER_ERROR);
    }
}

function qs_log($message) {
    $now = DateTime::createFromFormat('U.u', microtime(true));
    $ts = $now->format("Y-m-d\TH:i:s,u000+0000");

    $log = sprintf("%s [PHP] %s %s: %s\n", $ts, $_POST['wf_type'].':'.$_POST['stage'].':'.$_POST['trace_id'], $_POST['qs_description'], $message) ;
    
    file_put_contents(sprintf('%s/logs/quicksilver.log', $_ENV['HOME']), $log, FILE_APPEND);

    return $log;
}

ob_start();
echo qs_log('START');
$starttime = microtime(true);

register_shutdown_function(function($starttime) {
    $endtime = microtime(true);
    $error = error_get_last();

    if ($error) {
      $buffer = ob_get_clean();
      http_response_code(500);
      echo $buffer;
      echo qs_log(print_r($error, true));  
    }
    echo qs_log('ELAPSE: '. sprintf('%Fs', $endtime - $starttime));
    ob_end_flush();
}, $starttime);
