<?php

/**
 * 核心错误处理器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package core
 */
class CoreError {

    /**
     * 错误页面URL
     * @var string 
     */
    private $pageURL;

    /**
     * 初始化
     * @param string $message 消息
     * @param int $id 列
     * @param int $level 级别
     * @param string $url 跳转地址
     * @param boolean $debug Debug是否开启
     */
    public function __construct($message, $id = 0, $level = 0, $url = 'error.php', $debug = false) {
        if ($level != 2) {
            $this->addLog($id, $message . ' Level : ' . $level);
            if ($debug == false) {
                $this->pageURL = $url . '?e=';
                $this->error($message);
            } else {
                die('<p>Location : ' . $id . '</p>' . '<p>Message : ' . $message . '</p>' . '<p>Level : ' . $level . '</p>');
            }
        }
    }

    /**
     * 添加一条系统日志
     * @param string $local    位置
     * @param int $type    日志类型
     * @param string $message 消息
     */
    private function addLog($local, $type, $message) {
        openlog($local, LOG_PID | LOG_PERROR, LOG_LOCAL0);
        syslog($type, $message);
        closelog();
    }

    /**
     * 发送错误消息
     * @param string $message 错误消息
     */
    private function error($message) {
        $this->toURL($message);
    }

    /**
     * 将错误消息发送并跳转到URL
     * @param strng $message 错误消息
     */
    private function toURL($message) {
        try {
            header('Location:' . $this->pageURL . $message);
        } catch (Exception $e) {
            die($message);
        }
    }

}

/**
 * 错误接收函数
 * @since 4
 * @param string $errno 错误级别
 * @param string $errstr 错误描述
 * @param string $errfile 错误文件名
 * @param integer $errline 错误发生行
 */
function CoreErrorHandle($errno, $errstr, $errfile, $errline) {
    $message = $errno . ' : ' . $errstr;
    $id = $errfile . '::' . $errline;
    $coreError = new CoreError($message, $id, 2, ERROR_PAGE, DEBUG_ON);
}

//设定错误输出函数
set_error_handler('CoreErrorHandle');
?>
