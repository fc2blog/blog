<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

// For security reasons.
// if you want set enable, please see FC2_ERROR_ON_DISPLAY in config.sample.php
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
ini_set('html_errors', "0");

// Check all errors when shutdown applications.
register_shutdown_function(function () {
    $error = error_get_last();
    if (
        !is_array($error) ||
        !(
            $error['type'] &
            (E_ERROR | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR)
        )
    ) {
        return; // normal closing.
    }

    // abnormal end. try print gentle errors.

    // Logging un-excepted output buffer(debug|error messages)
    $something = ob_get_contents();
    if (strlen($something) > 0) {
        error_log($something);
    }
    ob_end_clean();

    // Error Logging
    error_log("Uncaught Fatal Error: {$error['type']}:{$error['message']} in {$error['file']}:{$error['line']}");

    // response error
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Internal Server Error</title>
    </head>
    <body>
        <h1>500 Internal Server Error</h1>
        <p>Something went wrong. / 処理中にエラーが発生しました。</p>
    HTML;
    if (defined("ERROR_ON_DISPLAY") && ERROR_ON_DISPLAY === "1") {
        echo "<hr><span style='color:red'>THE BLOG CONFIGURATION IS DANGER. PLEASE REMOVE `ERROR_ON_DISPLAY` in production.</span><br>";
        echo nl2br(htmlspecialchars($something . PHP_EOL, ENT_QUOTES));
        echo nl2br(htmlspecialchars("Uncaught Fatal Error: {$error['type']}:{$error['message']} in {$error['file']}:{$error['line']}"));
    }
    echo <<<HTML
    </body>
    </html>
    HTML;
});

try {
    // Catch all error (include notice/warn) and convert to ErrorException.
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    // enable output buffer
    ob_start();

    require_once(__DIR__ . '/../../vendor/autoload.php');

    // config.phpの存在チェック
    if (!file_exists(__DIR__ . '/../../config.php') && (string)getenv("FC2_CONFIG_FROM_ENV") !== "1") {
        header("Content-Type: text/html; charset=UTF-8");
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="ja">
        <head>
            <title>Does not exists config.php</title>
        </head>
        <body>
            Does not exists config.php / config.phpが存在しておりません
            <p class="ng">
                Please copy app/config.sample.php to app/config.php and edit them.<br>
                app/config.sample.phpをapp/config.phpにコピーしファイル内に存在するDBの接続情報とサーバーの設定情報を入力してください。
            </p>
        </body>
        </html>
        HTML;
        return;
    }

    // 設定クラス読み込み
    if ((string)getenv("FC2_CONFIG_FROM_ENV") === "1") {
        require(__DIR__ . '/../../config_read_from_env.php');
    } else {
        /** @noinspection PhpIncludeInspection */
        require(__DIR__ . '/../../config.php');
    }

    // APPディレクトリのパス
    define('APP_DIR', realpath(__DIR__ . '/../../') . '/');

    // タイムゾーン設定 TODO php.ini移譲でよいのではないか？
    date_default_timezone_set(\Fc2blog\App::$timesZone);

    // アプリケーション実行
    $request = new \Fc2blog\Web\Request();
    /** @var \Fc2blog\Web\Controller\Controller $c */
    $c = new $request->className($request);
    $c->execute($request->methodName);

    // TODO Logging un-excepted output buffer(debug|error messages|other)
    // $something = ob_get_contents();
    // if (strlen($something) > 0) {
    //     error_log($something);
    // }
    // ob_end_clean();
    // ob_start();
    // TODO remove all `echo` in app. ex: captcha

    $c->emit();
    ob_end_flush();
    return;

} catch (Throwable $e) {
    // Uncaught Exception

    // Logging un-excepted output buffer(debug|error messages)
    $something = ob_get_contents();
    if (strlen($something) > 0) {
        error_log($something);
    }
    ob_end_clean();

    // Stack trace Logging
    $error_class_name = get_class($e);
    error_log("Uncaught Exception {$error_class_name}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}");

    // response error
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Internal Server Error</title>
    </head>
    <body>
        <h1>500 Internal Server Error</h1>
        <p>Something went wrong. / 処理中にエラーが発生しました。</p>
    HTML;
    if (defined("ERROR_ON_DISPLAY") && ERROR_ON_DISPLAY === "1") {
        echo "<hr><span style='color:red'>THE BLOG CONFIGURATION IS DANGER. PLEASE REMOVE `ERROR_ON_DISPLAY` in production.</span><br>";
        echo nl2br(htmlspecialchars($something . PHP_EOL, ENT_QUOTES));
        echo nl2br(htmlspecialchars("Uncaught Exception {$error_class_name}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}"));
    }
    echo <<<HTML
    </body>
    </html>
    HTML;
    return;
}
