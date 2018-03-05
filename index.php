<?php
    /**
     * HuaWeiPush
     */
    $message = array(
        'title' => '我是标题',
        'content' => '我是内容~',
    );
    $deviceTokens[] = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    include('HuaWeiPush.class.php');
    $HuaWeiPush = new HuaWeiPush();
    $HuaWeiPush->send($message,$deviceTokens);
?>