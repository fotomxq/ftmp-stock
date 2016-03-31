<?php

/**
 * 备份还原系统
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package sys
 */
class SysBackup {

    private $db;
    private $backupDir;

    public function __construct(&$db, $backupDir) {
        $this->db = $db;
        $this->backupDir = $backupDir;
    }

    public function backup() {
        return false;
    }

    public function back() {
        return false;
    }

    public function viewList() {
        
    }

    public function upload() {
        
    }

    public function delete() {
        
    }

    public function download() {
        
    }

}

?>