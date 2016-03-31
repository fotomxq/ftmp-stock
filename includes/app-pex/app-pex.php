<?php

/**
 * PEX处理类
 * <p>将需要导入的文件放入import下，在界面中导入即可。</p>
 * @author liuzilu <fotomxq@gmail.com>
 * @version 51
 * @package app-pex
 */
class AppPex {

    /**
     * 数据库对象
     * @var CoreDB 
     */
    private $db;

    /**
     * 配置操作对象
     * @var SysConfig 
     */
    private $config;

    /**
     * 缓冲对象
     * @var CoreCache 
     */
    private $cache;

    /**
     * 初始化数据库文件路径
     * @var string 
     */
    private $dbSqliteSrc;

    /**
     * 分区数据内部缓冲
     * @var array
     */
    private $diskData;

    /**
     * 标签设计
     * @var array 
     */
    private $tags;

    /**
     * 选择的数据库数据
     * @var array 
     */
    private $diskSelect;

    /**
     * 当前操作的SQLite数据库句柄
     * @var CoreSQLite
     */
    private $sqlite;

    /**
     * 动作类型集合
     * @var array
     */
    private $actionData = array(
        array('name' => 'exclude-file-sha1', 'title' => '排除文件SHA1'),
        array('name' => 'exclude-file-type', 'title' => '排除文件类型')
    );

    /**
     * 日志控制器
     * @var CoreLog 
     */
    private $log;

    /**
     * 初始化
     * @param CoreDB $db 数据库对象
     * @param SysConfig $config 配置操作对象
     * @param CoreCache $cache 缓冲对象
     */
    public function __construct(&$db, &$config, &$cache) {
        $this->db = $db;
        $this->config = $config;
        $this->cache = $cache;
        $this->dbSqliteSrc = DIR_LIB . DS . 'app-pex' . DS . 'data.sqlite';
        $this->diskSelect = $this->getDiskSelect();
        $this->tags = json_decode($this->getTags(), true);
        $dbSrc = $this->getDiskDataDBSrc();
        if ($dbSrc) {
            $logDir = $this->getDiskLogDir();
            $this->log = new CoreLog(true, $logDir, 5, '192.168.1.1');
            if (!$this->connectSQLite($dbSrc)) {
                $this->addLog('app-pex::__construct::001', 'cannot connect disk sqlite,src:' . $dbSrc);
            }
        }
    }

    /**
     * 获取分区数据
     * @return array 数据数组
     */
    public function getDisk() {
        if ($this->diskData) {
            return $this->diskData;
        }
        $disk = $this->config->get('PEX-DISK');
        if (!$disk || $disk === '""') {
            return null;
        }
        $res = json_decode($disk, true);
        $this->diskData = $res;
        return $res;
    }

    /**
     * 获取选择的分区数据
     * @return array
     */
    public function getDiskSelect() {
        $name = $this->config->get('PEX-DISK-SELECT');
        $diskData = $this->getDisk();
        $res = null;
        if ($name) {
            foreach ($diskData as $v) {
                if ($v['name'] == $name) {
                    $res = $v;
                }
            }
        }
        if (!$name || !$res) {
            if ($diskData) {
                $res = $diskData[0];
            }
        }
        return $res;
    }

    /**
     * 设定选择的分区
     * @param string $name 分区名称
     * @return boolean 是否成功
     */
    public function saveDiskSelect($name) {
        //确保参数正确
        if (!$name) {
            return false;
        }
        //获取分区列表
        $diskList = $this->getDisk();
        if (!$diskList) {
            return false;
        }
        //检查是否在列表内
        $bool = false;
        foreach ($diskList as $v) {
            if ($v['name'] == $name) {
                $bool = true;
            }
        }
        if (!$bool) {
            return false;
        }
        //切换最后一次访问目录ID
        $this->saveLastDirID(0);
        //保存并返回
        return $this->config->save('PEX-DISK-SELECT', $name);
    }

    /**
     * 修改和添加分区
     * @param string $name 标识名称
     * @param string $title 标题
     * @param string $src 路径
     * @return boolean 是否成功
     */
    public function editDisk($name, $title, $src) {
        //确保参数正确
        if (!$name || !$title || !$src) {
            return false;
        }
        //获取分区列表
        $diskList = $this->getDisk();
        if (!$diskList) {
            return false;
        }
        //编辑分区
        $bool = false;
        $newArr = array('name' => $name, 'title' => $title, 'src' => $src);
        foreach ($diskList as $k => $v) {
            if ($name == $v['name']) {
                $diskList[$k] = $newArr;
                $bool = true;
            }
        }
        //如果分区不存在，则添加
        if (!$bool) {
            $diskList[] = $newArr;
        }
        //确保分区路径存在
        if (!CoreFile::isDir($src)) {
            if (!CoreFile::newDir($src)) {
                return false;
            }
        }
        //确保分区核心目录存在
        $diskImportSrc = $src . DS . 'import';
        $diskDataSrc = $src . DS . 'database';
        $diskFileSrc = $src . DS . 'data';
        $diskImportCacheSrc = $src . DS . 'import-cache';
        $diskFileCacheSrc = $src . DS . 'data-cache';
        $diskLogSrc = $src . DS . 'log';
        if (!CoreFile::newDir($diskImportSrc)) {
            return false;
        }
        if (!CoreFile::newDir($diskDataSrc)) {
            return false;
        }
        if (!CoreFile::newDir($diskFileSrc)) {
            return false;
        }
        if (!CoreFile::newDir($diskImportCacheSrc)) {
            return false;
        }
        if (!CoreFile::newDir($diskFileCacheSrc)) {
            return false;
        }
        if (!CoreFile::newDir($diskLogSrc)) {
            return false;
        }
        $diskBackupSrc = $diskDataSrc . DS . 'backups';
        if (!CoreFile::newDir($diskBackupSrc)) {
            return false;
        }
        //确保数据库存在
        $diskDbSrc = $diskDataSrc . DS . 'data.sqlite';
        if (!CoreFile::isFile($diskDbSrc)) {
            if (!CoreFile::copyFile($this->dbSqliteSrc, $diskDbSrc)) {
                return false;
            }
        }
        //确保标签库存在
        $diskTagSrc = $diskDataSrc . DS . 'tags.json';
        $tagSrc = DIR_LIB . DS . 'app-pex' . DS . 'tags.json';
        if (!CoreFile::isFile($diskTagSrc)) {
            if (!CoreFile::copyFile($tagSrc, $diskTagSrc)) {
                return false;
            }
        }
        //保存分区数据到数据库
        $bool = $this->saveDisk($diskList);
        //返回
        return $bool;
    }

    /**
     * 删除分区
     * @param string $name
     * @return boolean 是否成功
     */
    public function delDisk($name) {
        $diskList = $this->getDisk();
        if (!$diskList) {
            return false;
        }
        $newDisk = null;
        foreach ($diskList as $v) {
            if ($name == $v['name']) {
                continue;
            }
            $newDisk[] = $v;
        }
        return $this->saveDisk($newDisk);
    }

    /**
     * 保存分区数据到数据库
     * @param array $arr 数据数组
     * @return boolean 是否成功
     */
    private function saveDisk($arr) {
        $diskJson = json_encode($arr);
        return $this->config->save('PEX-DISK', $diskJson);
    }

    /**
     * 获取备份文件列表
     * @return array
     */
    public function getBackupList() {
        //获取备份文件路径
        $dir = $this->getDiskBackupDir();
        if (!$dir) {
            return null;
        }
        //搜索所有备份文件
        $search = $dir . DS . '*.backup';
        $res = CoreFile::searchDir($search);
        if ($res) {
            $newResV = null;
            foreach ($res as $k => $v) {
                $newResV[$k] = null;
                $newResV[$k]['name'] = CoreFile::getBasename($v);
                $newResV[$k]['size'] = round(filesize($v) / 1024 / 1024, 2);
            }
            $res = $newResV;
        }
        return $res;
    }

    /**
     * 建立新的备份
     * @param boolean $isAuto 是否为自动备份，开启则自动根据文件大小区别来备份数据库
     * @return boolean 是否成功
     */
    public function newBackup($isAuto = false) {
        //获取备份文件路径
        $dir = $this->getDiskBackupDir();
        if (!$dir) {
            return false;
        }
        //找到数据库路径
        $dbSrc = $this->getDiskDataDBSrc();
        if (!CoreFile::isFile($dbSrc)) {
            return false;
        }
        //最后一次备份的文件路径
        $lastBackupFileSrc = $dir . DS . 'data-last.sqlite.backup';
        //如果是自动备份
        //检查最后一次备份和当前数据库大小相差，是否超过100KB。如果小于，则返回成功
        if ($isAuto && CoreFile::isFile($lastBackupFileSrc)) {
            $lastBackupFileSize = filesize($lastBackupFileSrc) / 1024;
            $dbSize = filesize($dbSrc) / 1024;
            $sizeX = abs($dbSize - $lastBackupFileSize);
            if ($sizeX < 100) {
                return true;
            }
        }
        //构建新备份文件路径
        $newBackupFileSrc = $dir . DS . date('Ymd-H') . '.sqlite.backup';
        //检查文件是否存在，存在则删除
        if (CoreFIle::isFile($newBackupFileSrc)) {
            if (!CoreFile::deleteFile($newBackupFileSrc)) {
                return false;
            }
        }
        //拷贝数据库到新备份文件路径
        if (!CoreFile::copyFile($dbSrc, $newBackupFileSrc)) {
            return false;
        }
        //拷贝现有数据库到最后一次备份文件
        //如果文件不存在，则建立
        if (CoreFile::isFile($lastBackupFileSrc)) {
            if (!CoreFile::deleteFile($lastBackupFileSrc)) {
                return false;
            }
        }
        if (!CoreFile::copyFile($dbSrc, $lastBackupFileSrc)) {
            return false;
        }
        //成功后，获取所有备份文件，准备将备份文件分段存储
        //因为备份文件返回是按照正序返回的，所以将前面的10个转移走即可，避免文件过多
        $bkFiles = $this->getBackupList();
        if (count($bkFiles) > 20) {
            $newDir = $this->getDiskBackupHistoryDir();
            if (!$newDir) {
                return false;
            }
            for ($i = 0; $i < 10; $i++) {
                $v = $bkFiles[$i];
                $vDir = $newDir . DS . substr($v['name'], 0, 6);
                CoreFile::newDir($vDir);
                $vSrc = $vDir . DS . $v['name'];
                CoreFile::cutF($dir . DS . $v['name'], $vSrc);
            }
        }
        //返回成功
        return true;
    }

    /**
     * 还原数据库
     * @param string $fileName 文件名称，包括后缀部分
     * @return boolean 是否成功
     */
    public function returnBackup($fileName) {
        //获取备份文件路径
        $dir = $this->getDiskBackupDir();
        if (!$dir) {
            return null;
        }
        //数据库路径
        $dbSrc = $this->getDiskDataDBSrc();
        if (!CoreFile::isFile($dbSrc)) {
            return false;
        }
        //构建备份文件路径
        $backupSrc = $dir . DS . $fileName;
        //检查文件是否存在
        if (!CoreFile::isFile($backupSrc)) {
            return false;
        }
        //强制备份数据库
        if (!$this->newBackup()) {
            return false;
        }
        //将现有数据库删除，并复制该备份
        if (!CoreFile::deleteFile($dbSrc)) {
            return false;
        }
        if (!CoreFile::copyFile($backupSrc, $dbSrc)) {
            return false;
        }
        //清理缓冲
        $this->clearFileCache();
        //返回成功
        return true;
    }

    /**
     * 删除备份文件
     * @param string $fileName 文件名称
     * @return boolean 是否成功
     */
    public function delBackup($fileName) {
        //获取备份文件路径
        $dir = $this->getDiskBackupDir();
        if (!$dir) {
            return null;
        }
        //构建备份文件路径
        $backupSrc = $dir . DS . $fileName;
        //检查文件是否存在
        if (!CoreFile::isFile($backupSrc)) {
            return false;
        }
        return CoreFile::deleteFile($backupSrc);
    }

    /**
     * 获取标签数组
     * @return string JSON数据源
     */
    public function getTags() {
        $src = $this->getDiskDataTagSrc();
        if (!$src) {
            return null;
        }
        $res = CoreFile::loadFile($src);
        return $res;
    }

    /**
     * 保存标签到文件
     * @param string $json JSON数据
     * @return boolean 是否成功
     */
    public function saveTags($json) {
        $fileSrc = $this->getDiskDataTagSrc();
        if (!$fileSrc) {
            return false;
        }
        $jsonRes = json_decode($json, true);
        return CoreFile::saveFile($fileSrc, json_encode($jsonRes));
    }

    /**
     * 获取等待导入的文件
     * @param int $offset 文件列表偏离
     * @return array 数据数组
     */
    public function getImportWaitFile() {
        //获取等待导入文件目录路径
        $dir = $this->getImportSrc();
        if (!$dir) {
            return null;
        }
        //搜索所有文件夹
        $search = $dir . DS . '*';
        $fileList = CoreFile::searchDir($search, GLOB_ONLYDIR);
        //如果不存在文件夹，则返回空
        if (!$fileList) {
            //检查目录是否有遗留的非目录文件
            $delFileList = CoreFile::searchDir($search);
            if ($delFileList) {
                foreach ($delFileList as $v) {
                    CoreFile::deleteFile($v);
                }
            }
            //返回
            return null;
        }
        //如果存在文件夹，则抽取第一个数据，并清理内存
        $waitFile = null;
        $fileListCount = count($fileList);
        $waitFile['wait-folder-count'] = $fileListCount;
        $rand = rand(0, $fileListCount);
        if (isset($fileList[$rand])) {
            $waitFile['src'] = $fileList[$rand];
        } else {
            $waitFile['src'] = $fileList[0];
        }
        $fileList = null;
        //生成缓冲路径
        $cacheSrc = $waitFile['src'] . DS . 'cache.json';
        //查找缓冲文件是否存在，如果存在则直接解析返回
        if (CoreFile::isFile($cacheSrc)) {
            $res = CoreFile::loadFile($cacheSrc);
            if ($res) {
                //在返回之前，将文件存储到最后一次访问的记录文件中
                $lastCacheDataSrc = $this->getDiskDataSrc() . DS . 'import-last.json';
                CoreFile::copyFile($cacheSrc, $lastCacheDataSrc);
                return json_decode($res, true);
            }
        }
        //获取标题部分
        $waitFile['title'] = CoreFile::getBasename(CoreFilter::getGBKUTF8($waitFile['src']));
        //如果不存在缓冲文件，则说明未进行存档，则构建新的目录名称
        $newFolderSrc = $dir . DS . sha1($waitFile['src']);
        CoreFile::cutF($waitFile['src'], $newFolderSrc);
        $waitFile['src'] = $newFolderSrc;
        //转码
        $waitFile['src'] = CoreFilter::getGBKUTF8($waitFile['src']);
        //重新构建缓冲文件路径
        $cacheSrc = $newFolderSrc . DS . 'cache.json';
        //获取拒绝的文件
        $excludeSha1 = $this->getActionExcludeSha1Files();
        $excludeType = $this->getActionExcludeTypeFiles();
        //查询该文件夹下所有文件
        $searchFile = $waitFile['src'] . DS . '*';
        $waitFiles = CoreFile::searchDir($searchFile);
        //如果不存在文件，则删除该文件夹，并返回空
        if (!$waitFiles) {
            CoreFile::deleteDir($waitFile['src']);
            return null;
        }
        //将所有SHA1排列
        $fileSha1List = null;
        //计算文件个数和占用空间，并更改文件名为KEY值，确保字符符合标准
        $waitFile['size'] = 0;
        $waitFile['count-exclude'] = 0;
        $waitFile['count-exists'] = 0;
        $waitFile['files'] = null;
        $waitFile['types'] = null;
        $waitFile['sha1'] = '';
        $key = 1;
        foreach ($waitFiles as $k => $v) {
            $vBasicName = CoreFile::getBasename($v);
            //如果系统是win，则转格式为UTF-8
            $vBasicName = CoreFilter::getGBKUTF8($vBasicName);
            //忽略缓冲文件
            if ($vBasicName == 'cache.json') {
                continue;
            }
            //检查是否在排除文件名单
            $vSha1 = sha1_file($v);
            if ($excludeSha1 && in_array($vSha1, $excludeSha1)) {
                CoreFile::deleteFile($v);
                $waitFile['count-exclude'] += 1;
                continue;
            }
            $vFileType = strtolower(CoreFile::getFileType($v));
            if ($excludeType && in_array($vFileType, $excludeType)) {
                CoreFile::deleteFile($v);
                $waitFile['count-exclude'] += 1;
                continue;
            }
            //检查重复文件，如果重复，则删除该文件
            if ($fileSha1List) {
                if (in_array($vSha1, $fileSha1List)) {
                    CoreFile::deleteFile($v);
                    $waitFile['count-exclude'] += 1;
                    continue;
                }
            }
            //如果不重复，则将该文件列入序列中
            $fileSha1List[] = $vSha1;
            //获取相关数据
            $vSize = round(filesize($v) / 1024);
            $waitFile['size'] += $vSize;
            $newName = $key . '_' . $vSha1 . '.' . $vFileType;
            $vNewSrc = $waitFile['src'] . DS . $newName;
            $vFile = array('src' => CoreFilter::getGBKUTF8($vNewSrc), 'last-name' => $vBasicName, 'name' => $newName, 'size' => $vSize, 'type' => $vFileType, 'sha1' => $vSha1);
            //查询文件是否存在，标记并加入总量
            $vFile['db-exists'] = $this->searchFileSha1($vSha1);
            if ($vFile['db-exists']) {
                $waitFile['count-exists'] += 1;
            }
            //将数据存入总数据集
            $waitFile['files'][] = $vFile;
            //修改文件名称
            CoreFile::cutF($v, $vNewSrc);
            $key += 1;
            //计算类型数目
            if (isset($waitFile['types'][$vFileType])) {
                $waitFile['types'][$vFileType] += 1;
            } else {
                $waitFile['types'][$vFileType] = 0;
            }
            //计算SHA1总
            $waitFile['sha1'] .= $vSha1;
        }
        //计算总文件个数
        $waitFile['count'] = count($waitFile['files']);
        //计算MB级别大小
        $waitFile['size-mb'] = 0;
        if ($waitFile['size'] > 0) {
            $waitFile['size-mb'] = round($waitFile['size'] / 1024, 2);
        }
        //计算总SHA1
        $waitFile['sha1'] = sha1($waitFile['sha1']);
        //计算类型中最多的
        $waitFile['types-max'] = '';
        if ($waitFile['types']) {
            $waitFile['types-max'] = $waitFile['files'][0]['type'];
            $i = 0;
            foreach ($waitFile['types'] as $k => $v) {
                if ($v >= $i) {
                    $waitFile['types-max'] = $k;
                }
            }
        }
        //保存数据到缓冲文件
        CoreFile::saveFile($cacheSrc, json_encode($waitFile));
        //在返回之前，将文件存储到最后一次访问的记录文件中
        $lastCacheDataSrc = $this->getDiskDataSrc() . DS . 'import-last.json';
        CoreFile::copyFile($cacheSrc, $lastCacheDataSrc);
        //返回数据
        return $waitFile;
    }

    /**
     * 搜索文件SHA1是否存在
     * @param string $sha1 文件SHA1
     * @return boolean 是否存在
     */
    private function searchFileSha1($sha1) {
        $sql = 'select `id` from `file` where `sha1` = :sha1';
        $attrs = array(':sha1' => array($sha1, PDO::PARAM_STR));
        $res = $this->runSQL($sql, $attrs, 2);
        if ($res) {
            return true;
        }
        return false;
    }

    /**
     * 删除等待导入文件夹
     * @param string $fileSha1 文件SHA1，如果指定则删除指定的SHA1文件，否则删除整个文件夹
     * @return boolean 是否成功
     */
    public function delImportWaitFile($fileSha1 = null) {
        //获取最后一次访问的文件，如果不存在则返回
        $dir = $this->getDiskDataSrc();
        $src = $dir . DS . 'import-last.json';
        if (!CoreFile::isFile($src)) {
            return false;
        }
        //解析文件数据
        $folderInfoJson = CoreFile::loadFile($src);
        if (!$folderInfoJson) {
            return false;
        }
        $folderInfo = json_decode($folderInfoJson, true);
        if (!$folderInfo) {
            return false;
        }
        $src2 = $folderInfo['src'] . DS . 'cache.json';
        //找到文件所在目录路径
        $folderSrc = CoreFilter::getUTF8GBK($folderInfo['src']);
        if (!CoreFile::isDir($folderSrc)) {
            return false;
        }
        //如果指定了SHA1，则查询该文件位置，删除；否则删除整个文件夹。
        if ($fileSha1 && $folderInfo['files']) {
            foreach ($folderInfo['files'] as $k => $v) {
                if ($v['sha1'] == $fileSha1) {
                    if (!CoreFile::deleteFile($v['src'])) {
                        return false;
                    }
                    $folderInfo['count'] = abs($folderInfo['count']) - 1;
                    $folderInfo['count-exclude'] = abs($folderInfo['count-exclude']) + 1;
                    $folderInfo['size'] = abs($folderInfo['size']) - abs($v['size']);
                    $folderInfo['types'][$v['type']] = abs($folderInfo['types'][$v['type']]) - 1;
                    $this->addActionExcludeSha1($v['sha1']);
                    unset($folderInfo['files'][$k]);
                }
            }
            //更新cache.json
            CoreFile::saveFile($src, json_encode($folderInfo));
            CoreFile::saveFile($src2, json_encode($folderInfo));
        } else {
            //将该文件夹删除，然后将缓冲的最后一访问数据删除
            if (!CoreFile::deleteDir($folderSrc)) {
                return false;
            }
            if (!CoreFile::deleteFile($src)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 导入文件
     * @param string $sha1 文件SHA1
     * @param int $parent 上一级ID
     * @param int $parentMarge 是否和上一级合并，如果生效则其他所有参数都无效，不包括$parent
     * @param int $rating 星级
     * @param string $title 标题
     * @param string $des 描述
     * @param array $tags 标签组
     * @return boolean 是否成功
     */
    public function importFile($sha1, $parent, $parentMarge, $rating, $title, $des, $tags) {
        //获取最后一次访问的文件，如果不存在则返回
        $dir = $this->getDiskDataSrc();
        $src = $dir . DS . 'import-last.json';
        if (!CoreFile::isFile($src)) {
            $this->addLog('app-pex::importFile::001', 'no select disk.');
            return false;
        }
        //解析文件数据
        $folderInfoJson = CoreFile::loadFile($src);
        if (!$folderInfoJson) {
            $this->addLog('app-pex::importFile::002', 'no folder info..');
            return false;
        }
        $folderInfo = json_decode($folderInfoJson, true);
        if (!$folderInfo) {
            $this->addLog('app-pex::importFile::003', 'cannot dejson folder info.');
            return false;
        }
        //检查SHA1是否匹配
        if ($sha1 !== $folderInfo['sha1']) {
            $this->addLog('app-pex::importFile::014', 'not this sha1,post sha1:' . json_encode($sha1) . ',folder should sha1:' . json_encode($folderInfo['sha1']) . '.');
            return false;
        }
        //找到文件所在目录路径
        $folderSrc = CoreFilter::getUTF8GBK($folderInfo['src']);
        if (!CoreFile::isDir($folderSrc)) {
            $this->addLog('app-pex::importFile::004', 'folder not ex.');
            return false;
        }
        //检查上一级ID，是否为目录
        $parentInfo = null;
        if ($parent > 0) {
            $parentInfo = $this->getFile($parent);
            if (!$parentInfo) {
                $this->addLog('app-pex::importFile::005', 'no parent folder info.');
                return false;
            }
            if (!$parentInfo['is_folder']) {
                $this->addLog('app-pex::importFile::006', 'parent not folder.');
                return false;
            }
            $parent = $parentInfo['id'];
        } else {
            $parentMarge = false;
        }
        //分区文件存储路径
        $diskDataFileSrc = $this->getDiskDataFileSrc();
        //初始化基本值
        $folderID = 0;
        $newFolderSrc = '';
        //如果不是合并文件夹，则建立文件夹
        if (!$parentMarge) {
            //构建新的文件夹路径
            $newFolderSrc = date('Ym') . DS . date('dH');
            $newFolderSrcRoot = $diskDataFileSrc . DS . $newFolderSrc;
            if (!CoreFile::newDir($newFolderSrcRoot)) {
                $this->addLog('app-pex::importFile::007', 'cannot create new folder.');
                return false;
            }
            $newFolderSrcRoot .= DS . $folderInfo['sha1'];
            $newFolderSrc .= DS . $folderInfo['sha1'];
            //构建文件夹SQL数据
            $sqlFolder = 'insert into `file`(`id`,`parent`,`is_folder`,`sort`,`star`,`title`,`des`,`sha1`,`size`,`src`,`type`) values(null,:parent,1,0,:rating,:title,:des,:sha1,:size,:src,\'\')';
            $attrsFolder = array(
                ':parent' => array($parent, PDO::PARAM_INT),
                ':rating' => array($rating, PDO::PARAM_INT),
                ':title' => array($title, PDO::PARAM_STR),
                ':des' => array($des, PDO::PARAM_STR),
                ':sha1' => array($folderInfo['sha1'], PDO::PARAM_STR),
                ':size' => array($folderInfo['size'], PDO::PARAM_STR),
                ':src' => array($newFolderSrc, PDO::PARAM_STR)
            );
            //执行SQL，生成文件夹ID
            $folderID = $this->runSQL($sqlFolder, $attrsFolder, 4);
            if ($folderID < 1) {
                $this->addLog('app-pex::importFile::008', 'cannot create folder sql.');
                return false;
            }
            //修改文件夹sort为自身ID
            $sqlFolderEditSort = 'update `file` set `sort` = `id` where `id` = :id';
            $attrsFolderEditSort = array(':id' => array($folderID, PDO::PARAM_INT));
            if (!$this->runSQL($sqlFolderEditSort, $attrsFolderEditSort, 0)) {
                $this->delFile($folderID);
                $this->addLog('app-pex::importFile::009', 'cannote edit folder sort.');
                return false;
            }
            //转移等待导入的文件夹到新的文件夹路径
            if (!CoreFile::cutF($folderSrc, $newFolderSrcRoot)) {
                $this->delFile($folderID);
                $this->addLog('app-pex::importFile::010', 'cannot cut folder to new src.');
                return false;
            }
            //生成文件夹对应的所有TAGS
            if ($tags) {
                $sqlTags = 'insert into `file_tag`(`file_id`,`tag_key`,`tag_type`) values(';
                $attrsTags = null;
                $sqlTagsArr = null;
                $tagType = (int) $tags['type'];
                foreach ($tags['tags'] as $k => $v) {
                    $vName = ':tag' . $k;
                    $sqlTagsArr[] = ((int) $folderID) . ',' . $vName . ',' . $tagType;
                    $attrsTags[$vName] = array($v, PDO::PARAM_STR);
                }
                $sqlTags .= implode('),(', $sqlTagsArr) . ')';
                if (!$this->runSQL($sqlTags, $attrsTags, 4)) {
                    $this->addLog('app-pex::importFile::011', 'cannot create folder tags sql.');
                    return false;
                }
            }
        } else {
            //如果是并入文件夹，则复制上一级文件夹信息
            $folderID = $parentInfo['id'];
            $newFolderSrc = $parentInfo['src'];
            //读取上一级文件夹下的cache.json文件数据
            $parentCacheSrc = $diskDataFileSrc . DS . $newFolderSrc . DS . 'cache.json';
            $parentCacheJson = CoreFile::loadFile($parentCacheSrc);
            $parentCache = json_decode($parentCacheJson, true);
            //将所有文件迁移到目录下，同时将文件信息再入到$parentCache中
            foreach ($folderInfo['files'] as $k => $v) {
                $v['name'] = date('YmdHis') . '_' . $v['name'];
                $folderInfo['files'][$k]['name'] = $v['name'];
                $vFileSrc = $newFolderSrc . DS . $v['name'];
                CoreFile::cutF($v['src'], $diskDataFileSrc . DS . $vFileSrc);
                $parentCache['files'][] = $v;
            }
            //叠加文件夹大小
            $parentCache['size'] = abs($parentCache['size']) + abs($folderInfo['size']);
            $sqlEditParentSize = 'update `file` set `size` = \'' . $parentCache['size'] . '\' where `id` = :id';
            $attrsEditParentSize = array(':id' => array($folderID, PDO::PARAM_INT));
            $this->runSQL($sqlEditParentSize, $attrsEditParentSize, 0);
            //写入cache.json
            $newParentCacheJson = json_encode($parentCache);
            CoreFile::saveFile($parentCacheSrc, $newParentCacheJson);
            //删除无用的导入文件夹
            CoreFile::deleteDir($folderSrc);
            //清理内存
            $parentCacheJson = null;
            $parentCache = null;
            $newParentCacheJson = null;
        }
        //生成子文件SQL
        $sqlFilesArrs = null;
        $rating = (int) $rating;
        foreach ($folderInfo['files'] as $k => $v) {
            $vSrc = $newFolderSrc . DS . $v['name'];
            $sqlFilesArrs[] = 'null,' . $folderID . ',0,' . $k . ',' . $rating . ',\'' . $v['name'] . '\',\'\',\'' . $v['sha1'] . '\',\'' . $v['size'] . '\',\'' . $vSrc . '\',\'' . $v['type'] . '\'';
        }
        $sqlFiles = 'insert into `file`(`id`,`parent`,`is_folder`,`sort`,`star`,`title`,`des`,`sha1`,`size`,`src`,`type`) values(' . implode('),(', $sqlFilesArrs) . ')';
        //执行文件生成SQL
        if (!$this->runSQL($sqlFiles, null, 4)) {
            $this->addLog('app-pex::importFile::012', 'cannot create file sql.');
            return false;
        }
        //全部完成后，删除最后一次访问的缓冲数据
        if (!CoreFile::deleteFile($src)) {
            $this->addLog('app-pex::importFile::013', 'cannot delete file last-import.json.');
            return false;
        }
        //将标签数据保存到最后一次设定数据组
        $this->saveImportLastSet($parent, null, null, $tags);
        //清理缓冲
        $this->clearFileCache();
        //返回成功
        return true;
    }

    /**
     * 获取所有模版数据
     * @return array 模版数据数组
     */
    public function getTagsTemplate() {
        //获取数据路径
        $dir = $this->getDiskTagsTemplateSrc();
        if (!$dir) {
            return null;
        }
        //获取路径下所有JSON文件
        $search = $dir . DS . '*.json';
        $res = CoreFile::searchDir($search);
        if (!$res) {
            return null;
        }
        //整理数据
        $newRes = null;
        foreach ($res as $v) {
            $vJson = CoreFile::loadFile($v);
            $vRes = json_decode($vJson, true);
            $newRes[] = $vRes;
        }
        //返回
        return $newRes;
    }

    /**
     * 将标签模版存储到文件
     * @param string $name 标识
     * @param string $title 名称
     * @param array $tags 标签组
     * @return boolean 是否成功
     */
    public function saveTagsTemplate($name, $title, $tags) {
        //检查基本参数
        if (!$name || !$title) {
            return false;
        }
        //获取数据路径
        $dir = $this->getDiskTagsTemplateSrc();
        if (!$dir) {
            return false;
        }
        //构建文件路径
        $src = $dir . DS . $name . '.json';
        //存储数据到文件
        $res = array('name' => $name, 'title' => $title, 'tags' => $tags);
        $resJson = json_encode($res);
        return CoreFile::saveFile($src, $resJson);
    }

    /**
     * 删除标签模版文件
     * @param string $name 标识名称
     * @return boolean 是否成功
     */
    public function delTagsTemplate($name) {
        //检查基本参数
        if (!$name) {
            return false;
        }
        //获取数据路径
        $dir = $this->getDiskTagsTemplateSrc();
        if (!$dir) {
            return false;
        }
        //构建文件路径
        $src = $dir . DS . $name . '.json';
        //检查文件是否存在
        if (!CoreFile::isFile($src)) {
            return false;
        }
        //删除并返回
        return CoreFile::deleteFile($src);
    }

    /**
     * 输出预览数据
     * @param int $key 要读取的文件KEY值
     * @param int $max 图像高宽最大值
     */
    public function importPreView($key, $max = 500) {
        //最后一次访问的缓冲文件路径
        $diskDataSrc = $this->getDiskDataSrc();
        $cacheSrc = $diskDataSrc . DS . 'import-last.json';
        //确保文件存在
        if (!CoreFile::isFile($cacheSrc)) {
            die();
        }
        //读取该文件信息
        $cacheFileC = CoreFile::loadFile($cacheSrc);
        if (!$cacheFileC) {
            die();
        }
        //解析数据
        $data = json_decode($cacheFileC, true);
        if (!$data) {
            die();
        }
        //确保文件数据准确
        if (!isset($data['files'][$key])) {
            die();
        }
        //分析数据，获取文件路径
        $src = CoreFilter::getUTF8GBK($data['files'][$key]['src']);
        $type = CoreFile::getFileType($src);
        //检查文件是否存在，如果不存在，则结束
        if (!CoreFile::isFile($src)) {
            die();
        }
        //如果是图像的话，根据SHA1值合成图像缓冲路径
        $diskImportCacheSrc = $this->getImportCacheSrc();
        $importCache = new CoreCache(true, 3600, $diskImportCacheSrc);
        //根据文件类型，输出数据
        $res = null;
        CoreHeader::noCache();
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $importCache->img($src, $max, $max, true);
                break;
            case 'png':
                $importCache->img($src, $max, $max, true);
                break;
            case 'gif':
                //$res = imagecreatefromgif($src);
                //imagegif($res);
                $res = CoreFile::loadFile($src);
                CoreHeader::toImg('gif');
                die($res);
                break;
            case 'txt':
                $res = CoreFile::loadFile($src);
                CoreHeader::outHTML($res);
                break;
            default:
                $res = CoreFile::loadFile($src);
                CoreHeader::outHTML($res);
                break;
        }
        die();
    }

    /**
     * 获取文件基本信息
     * @param int $id 文件ID
     * @return array 数据数组
     */
    public function getFile($id) {
        $sql = 'select * from `file` where `id` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        $res = $this->runSQL($sql, $attrs, 1, PDO::FETCH_ASSOC);
        if (!$res) {
            return null;
        }
        $dir = $this->getDiskDataFileSrc();
        $res['root-src'] = $dir . DS . $res['src'];
        $res['tags'] = null;
        $sqlTags = 'select `tag_key`,`tag_type` from `file_tag` where `file_id` = :id';
        $resTags = $this->runSQL($sqlTags, $attrs, 3, PDO::FETCH_ASSOC);
        if ($resTags) {
            if (is_array($resTags)) {
                foreach ($resTags as $v) {
                    $res['tags'][] = $v['tag_key'];
                }
                $res['tags-type'] = $resTags[0]['tag_type'];
            }
        }
        return $res;
    }

    /**
     * 获取文件列表
     * @param int $parent 上一级ID，如果指定$onlyType为all-级别，则忽略该参数
     * @param string $onlyType 显示类型,normal/only-file/only-folder/all-file/all-folder
     * @param array $tags 标签组,eg:array('type'=>'','tags'=>array('0-1-2','2-3-4',...))
     * @param int $star 喜爱程度
     * @param boolean $tagIsor 标签关联是否为或，否则为和
     * @param int $page 也是
     * @param int $max 页长
     * @param int $sort 排序键位
     * @param boolean $desc 是否倒叙
     * @return array 数据数组
     */
    public function getFiles($parent = 0, $onlyType = null, $tags = null, $star = null, $tagIsor = true, $page = 1, $max = 10, $sort = 3, $desc = false) {
        //无论成功与否，设定pex-last-dir-id = parent
        $this->saveLastDirID($parent);
        //构建缓冲路径
        $cacheDir = $this->getDataFileCacheSrc();
        $cache = new CoreCache(true, 3600, $cacheDir);
        $cacheName = sha1($parent . $onlyType . json_encode($tags) . $star . json_encode($tagIsor) . $page . $max . $sort . json_encode($desc));
        //获取缓冲，如果成功则返回数据
        $cacheStr = $cache->get($cacheName);
        if ($cacheStr) {
            return json_decode($cacheStr, true);
        }
        //初始化SQL&attrs
        $sql = '';
        $attrs = null;
        //单独访问类型，只读文件或目录，或全部文件或目录
        $sqlOnlyType = '';
        switch ($onlyType) {
            case 'only-file':
            case 'all-file':
                $sqlOnlyType = ' and file.is_folder = \'0\'';
                break;
            case 'only-folder':
            case 'all-folder':
                $sqlOnlyType = ' and file.is_folder = \'1\'';
                break;
            default:
                $sqlOnlyType = '';
                break;
        }
        //是否为全体类型数据，或某个目录下的类型数据
        $sqlParent = '';
        if ($onlyType === 'all-file' || $onlyType === 'all-folder') {
            $sqlParent = '';
        } else {
            $sqlParent = ' and file.parent = :parent';
            $attrs[':parent'] = array($parent, PDO::PARAM_INT);
        }
        //是否包含喜欢程度
        $sqlStar = '';
        if ($star !== null) {
            $sqlStar = ' and file.star = :star';
            $attrs['star'] = array($star, PDO::PARAM_INT);
        }
        //页数
        $sortKey = array('id', 'parent', 'is_folder', 'sort', 'star', 'title', 'des', 'sha1', 'size', 'src', 'type');
        $sqlSort = isset($sortKey[$sort]) ? $sortKey[$sort] : $sortKey[3];
        $sqlDesc = $desc ? 'desc' : 'asc';
        $sqlPage = ' order by file.' . $sqlSort . ' ' . $sqlDesc . ' limit ' . (($page - 1) * $max) . ',' . $max;
        //是否有标签选择
        if ($tags && $tags['tags']) {
            //遍历所有标签
            $sqlTags = '';
            foreach ($tags['tags'] as $k => $v) {
                $vName = ':tag' . $k;
                $sqlTags[] = '(file_tag.tag_key = ' . $vName . ' and (file_tag.file_id = file.id or file_tag.file_id = file.parent))';
                $attrs[$vName] = array($v, PDO::PARAM_STR);
            }
            $sqlTagOrAnd = $tagIsor ? ' or ' : ' and ';
            $sql = 'select distinct file.* from `file`,`file_tag` where file_tag.tag_type = :tagType and (' . implode($sqlTagOrAnd, $sqlTags) . ')';
            $attrs[':tagType'] = array($tags['type'], PDO::PARAM_INT);
        } else {
            $sql = 'select distinct file.* from `file` where 1';
        }
        //合并其他sql
        $sql = $sql . $sqlOnlyType . $sqlParent . $sqlStar . $sqlPage;
        //执行SQL
        $res = $this->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
        //加密标题和描述部分语句
        $newRes = null;
        if ($res) {
            foreach ($res as $k => $v) {
                $v['title'] = $this->encode($v['title']);
                $v['des'] = $this->encode($v['des']);
                $newRes[] = $v;
            }
        }
        //将数据保存到缓冲文件
        $cache->set($cacheName, json_encode($newRes));
        //返回数据
        return $newRes;
    }

    /**
     * 输出文件数据
     * 如果是图像则直接输出图像，其他文件类型根据情况输出
     * @param int $id 文件ID
     * @param int $w 宽度
     * @param int $h 高度
     */
    public function getFileView($id, $w, $h) {
        //获取文件信息
        $fileInfo = $this->getFile($id);
        if (!$fileInfo) {
            die();
        }
        //如果是文件夹
        if ($fileInfo['is_folder']) {
            CoreHeader::toURL('assets/imgs/file-img.png');
            die();
        }
        //cache
        $cacheDir = $this->getDataFileCacheSrc();
        $cache = new CoreCache(true, 3600, $cacheDir);
        //分辨文件类型，根据类型输出文件
        switch ($fileInfo['type']) {
            case 'jpg':
            case 'jpeg':
            case 'png':
                $cache->img($fileInfo['root-src'], $w, $h, true);
                break;
            case 'gif':
                $imgRes = CoreFile::loadFile($fileInfo['root-src']);
                CoreHeader::toImg('gif');
                die($imgRes);
                break;
            case 'txt':
                $imgRes = CoreFile::loadFile($fileInfo['root-src']);
                CoreHeader::outHTML($imgRes);
                break;
            default:
                $src = $fileInfo['root-src'];
                $fileSize = filesize($src);
                CoreHeader::downloadFile($fileSize, $fileInfo['title'], $src);
                return null;
                break;
        }
    }

    /**
     * 修改文件信息
     * @param int $id ID
     * @param int $parent 上一级ID
     * @param int $sort 排序
     * @param int $star 星级
     * @param string $title 标题
     * @param string $des 描述
     * @param array $tags 标签组,eg:array('type'=>'','tags'=>array('0-1-2','2-3-4',...))
     * @return boolean 是否成功
     */
    public function editFile($id, $parent = null, $sort = null, $star = null, $title = null, $des = null, $tags = null) {
        //获取该ID数据
        $fileInfo = $this->getFile($id);
        if (!$fileInfo) {
            return false;
        }
        //SQL和Attrs
        $sql = '';
        $attrs = null;
        //修改上一级ID
        if ($parent !== null && $fileInfo['parent'] !== $parent) {
            $sql .= ',`parent` = :parent';
            $attrs[':parent'] = array($parent, PDO::PARAM_INT);
        }
        //修改排序
        if ($sort !== null && $fileInfo['sort'] !== $sort) {
            $sql .= ',`sort` = :sort';
            $attrs[':sort'] = array($sort, PDO::PARAM_INT);
        }
        //修改星级
        if ($star !== null && $fileInfo['star'] !== $star) {
            $sql .= ',`star` = :star';
            $attrs[':star'] = array($star, PDO::PARAM_INT);
        }
        //修改标题
        if ($title !== null && $fileInfo['title'] !== $title) {
            $sql .= ',`title` = :title';
            $attrs[':title'] = array($title, PDO::PARAM_STR);
        }
        //修改描述
        if ($des !== null && $fileInfo['des'] !== $des) {
            $sql .= ',`des` = :des';
            $attrs[':des'] = array($des, PDO::PARAM_STR);
        }
        //如果没有任何数据，则返回
        if (!$sql) {
            return true;
        }
        //合成SQL
        $sql = 'update `file` set ' . substr($sql, 1) . ' where `id` = :id';
        $attrs[':id'] = array($id, PDO::PARAM_INT);
        //执行SQL
        $bool = $this->runSQL($sql, $attrs, 0);
        if (!$bool) {
            return false;
        }
        //处理标签组
        if ($tags !== null) {
            if ($tags && is_array($tags) && $tags['tags']) {
                //相关sql
                $sqlTagAdd = '';
                $attrsTagAdd = null;
                //遍历提交数据，在已存在数据中查询提交数据，如果不存在，则建立
                foreach ($tags['tags'] as $k => $v) {
                    if ($fileInfo['tags'] && in_array($v, $fileInfo['tags'])) {
                        //该标签存在
                    } else {
                        //该标签不存在，建立
                        $vName = ':tagAdd' . $k;
                        $vs = explode('-', $v);
                        $sqlTagAdd .= ',(' . $fileInfo['id'] . ',' . $vName . ',\'' . (int) $vs[0] . '\')';
                        $attrsTagAdd[$vName] = array($v, PDO::PARAM_STR);
                    }
                }
                if ($sqlTagAdd) {
                    $sqlTagAdd = 'insert into `file_tag`(`file_id`,`tag_key`,`tag_type`) values' . substr($sqlTagAdd, 1);
                    $boolTagAdd = $this->runSQL($sqlTagAdd, $attrsTagAdd, 0);
                    if (!$boolTagAdd) {
                        return false;
                    }
                }
            }
            if ($fileInfo['tags'] && is_array($tags)) {
                //遍历已存在数据，在提交数据中查询已存在数据，如果不存在，则删除
                $sqlTagDel = '';
                $attrsTagDel = null;
                foreach ($fileInfo['tags'] as $k => $v) {
                    if (in_array($v, $tags['tags'])) {
                        //该标签存在
                    } else {
                        //该标签不存在，建立
                        $vName = ':tagDel' . $k;
                        $sqlTagDel .= ' or `tag_key` = ' . $vName;
                        $attrsTagDel[$vName] = array($v, PDO::PARAM_STR);
                    }
                }
                if ($sqlTagDel) {
                    $sqlTagDel = 'delete from `file_tag` where (' . substr($sqlTagDel, 4) . ') and `file_id` = ' . $fileInfo['id'];
                    $boolTagDel = $this->runSQL($sqlTagDel, $attrsTagDel, 0);
                    if (!$boolTagDel) {
                        return false;
                    }
                }
            }
        }
        //清理缓冲
        $this->clearFileCache();
        //返回
        return true;
    }

    /**
     * 移动文件
     * @param int $targetID 目标文件夹ID
     * @param array $ids 源文件ID
     * @return boolean 是否成功
     */
    public function moveFile($targetID, $ids) {
        //确保源文件ID是数组
        if (!is_array($ids)) {
            return false;
        }
        //确保是文件夹
        $parentInfo = $this->getFile($targetID);
        if (!$parentInfo && $targetID > 0) {
            return false;
        } else {
            if (!$parentInfo['is_folder']) {
                return false;
            }
        }
        if ($targetID < 1) {
            $diskSrc = $this->getDiskDataFileSrc();
            $parentInfo = null;
            $parentInfo['id'] = 0;
            $parentInfo['src'] = date('Ym') . DS . date('dH') . DS . sha1(date('YmdHis'));
            $parentInfo['root-src'] = $diskSrc . DS . $parentInfo['src'];
            CoreFile::newDir($parentInfo['root-src']);
        }
        //生成SQL，并且将文件移动到新路径
        foreach ($ids as $k => $v) {
            //验证文件并获取信息
            $vInfo = $this->getFile($v);
            if (!$vInfo) {
                return false;
            }
            //获取文件新路径
            $vName = $vInfo['sha1'] . '.' . $vInfo['type'];
            $newSrc = $parentInfo['src'] . DS . $vName;
            $newRootSrc = $parentInfo['root-src'] . DS . $vName;
            //移动文件到新路径
            if (!CoreFile::cutF($vInfo['root-src'], $newRootSrc)) {
                return false;
            }
            //生成SQL
            $sql = 'update `file` set `parent` = :targetID,`src` = :src where `id` = :id';
            $attrs = array(
                ':id' => array($vInfo['id'], PDO::PARAM_INT),
                ':targetID' => array($parentInfo['id'], PDO::PARAM_INT),
                ':src' => array($newSrc, PDO::PARAM_STR)
            );
            //执行SQL
            $bool = $this->runSQL($sql, $attrs, 0);
            if (!$bool) {
                return false;
            }
        }
        //清理缓冲
        $this->clearFileCache();
        //返回
        return true;
    }

    /**
     * 合并文件夹
     * @param int $targetID 目标文件
     * @param int $srcID 源文件
     * @return boolean 是否成功
     */
    public function margeFile($targetID, $srcID) {
        //确保是文件夹
        $targetInfo = $this->getFile($targetID);
        if (!$targetInfo) {
            return false;
        }
        if (!$targetInfo['is_folder']) {
            return false;
        }
        $srcInfo = $this->getFile($srcID);
        if (!$srcInfo) {
            return false;
        }
        if (!$srcInfo['is_folder']) {
            return false;
        }
        //获取源文件下所有文件数据
        $sqlSrcFileList = 'select `id` from `file` where `parent` = :parent';
        $attrsSrcFileList = array(':parent' => array($srcID, PDO::PARAM_INT));
        $srcFileList = $this->runSQL($sqlSrcFileList, $attrsSrcFileList, 3, PDO::FETCH_ASSOC);
        //如果不存在任何子文件
        if (!$srcFileList) {
            return false;
        }
        //生成SQL，并且将文件移动到新路径
        foreach ($srcFileList as $k => $v) {
            //验证文件并获取信息
            $vInfo = $this->getFile($v['id']);
            if (!$vInfo) {
                return false;
            }
            //获取文件新路径
            $vName = CoreFile::getBasename($vInfo['src']);
            $newSrc = $targetInfo['src'] . DS . $vName;
            $newRootSrc = $targetInfo['root-src'] . DS . $vName;
            //移动文件到新路径
            if (!CoreFile::cutF($vInfo['root-src'], $newRootSrc)) {
                return false;
            }
            //生成SQL
            $sql = 'update `file` set `parent` = :targetID,`src` = :src where `id` = :id';
            $attrs = array(
                ':id' => array($vInfo['id'], PDO::PARAM_INT),
                ':targetID' => array($targetInfo['id'], PDO::PARAM_INT),
                ':src' => array($newSrc, PDO::PARAM_STR)
            );
            //执行SQL
            $bool = $this->runSQL($sql, $attrs, 0);
            if (!$bool) {
                return false;
            }
        }
        //最后删除源文件
        $this->delFile($srcID);
        //清理缓冲
        $this->clearFileCache();
        //返回
        return true;
    }

    /**
     * 调换文件顺序
     * @param int $targetID 目标文件ID
     * @param int $srcID 源文件ID
     * @return boolean 是否成功
     */
    public function sortFile($targetID, $srcID) {
        //获取两个文件信息
        $targetInfo = $this->getFile($targetID);
        if (!$targetInfo) {
            return false;
        }
        $srcInfo = $this->getFile($srcID);
        if (!$srcInfo) {
            return false;
        }
        //编辑两个文件信息，互换顺序
        if (!$this->editFile($targetInfo['id'], null, $srcInfo['sort'])) {
            return false;
        }
        if (!$this->editFile($srcInfo['id'], null, $targetInfo['sort'])) {
            return false;
        }
        //返回
        return true;
    }

    /**
     * 删除文件
     * @param int $id 文件ID
     * @return boolean 是否成功
     */
    public function delFile($id) {
        //如果是数组，则进入自循环处理
        if (is_array($id)) {
            foreach ($id as $v) {
                if (!$this->delFile($v)) {
                    return false;
                }
            }
            return true;
        }
        //获取文件信息
        $fileInfo = $this->getFile($id);
        if (!$fileInfo) {
            return false;
        }
        //查询文件夹，并删除
        if (CoreFile::isDir($fileInfo['root-src'])) {
            CoreFile::deleteDir($fileInfo['root-src']);
        }
        if (CoreFile::isFile($fileInfo['root-src'])) {
            CoreFile::deleteFile($fileInfo['root-src']);
        }
        if ($fileInfo['is_folder']) {
            //如果是文件夹，则递归
            $fileList = $this->getFiles($id);
            if ($fileList) {
                foreach ($fileList as $v) {
                    if (!$this->delFile($v['id'])) {
                        return false;
                    }
                }
            }
        }
        //生成删除SQL
        $sql = 'delete from `file` where `id` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        $res = $this->runSQL($sql, $attrs, 0);
        if (!$res) {
            return false;
        }
        //删除TAGS
        $sqlTags = 'delete from `file_tag` where `file_id` = :id';
        $res = $this->runSQL($sqlTags, $attrs, 0);
        //清理缓冲
        $this->clearFileCache();
        //返回
        return $res;
    }

    /**
     * 获取最后一次访问的目录ID
     * @return int ID
     */
    public function getLastDirID() {
        return $this->config->get('PEX-LAST-DIR-ID');
    }

    /**
     * 获取最后一次导入文件设定
     * @return array 数据数组
     */
    public function getImportLastSet() {
        /* 取消了数据库存储该数据，代为文件存储，但保留代码
          $res['prev-max'] = $this->config->get('PEX-IMPORT-PREV-MAX');
          $res['prev-size'] = $this->config->get('PEX-IMPORT-PREV-SIZE');
          $res['tags'] = $this->config->get('PEX-IMPORT-TAGS');
          if ($res['tags']) {
          $res['tags'] = json_decode($res['tags'], true);
          }
         */
        $res = array('prev-max' => 10, 'prev-size' => 150, 'tags' => null);
        $fileSrc = $this->getImportLastSetSrc();
        if (!$fileSrc) {
            return null;
        }
        if (CoreFile::isFile($fileSrc)) {
            $resJson = CoreFile::loadFile($fileSrc);
            $res = json_decode($resJson, true);
        }
        $res['parent'] = $this->getLastDirID();
        return $res;
    }

    /**
     * 保存导入文件最后一次设定
     * @param int $parent 上一级ID
     * @param int $prevMax 预览文件个数
     * @param int $prevSize 预览图像尺寸
     * @param array $tags 标签组
     * @return boolean
     */
    public function saveImportLastSet($parent, $prevMax, $prevSize, $tags) {
        if ($parent !== null) {
            $this->saveLastDirID($parent);
        }
        /* 取消了数据库存储该数据，代为文件存储，但保留代码
          if ($prevMax !== null) {
          $this->config->save('PEX-IMPORT-PREV-MAX', $prevMax);
          }
          if ($prevSize !== null) {
          $this->config->save('PEX-IMPORT-PREV-SIZE', $prevSize);
          }
          if ($tags !== null) {
          $this->config->save('PEX-IMPORT-TAGS', json_encode($tags));
          }
         */
        $fileSrc = $this->getImportLastSetSrc();
        if (!$fileSrc) {
            return false;
        }
        $res = $this->getImportLastSet();
        if ($prevMax !== null) {
            $res['prev-max'] = $prevMax;
        }
        if ($prevSize !== null) {
            $res['prev-size'] = $prevSize;
        }
        if ($tags !== null) {
            $res['tags'] = $tags;
        }
        $resJson = json_encode($res);
        return CoreFile::saveFile($fileSrc, $resJson);
    }

    /**
     * 获取导入文件设定数据文件路径
     * @return string 路径
     */
    private function getImportLastSetSrc() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return '';
        }
        //获取
        $res = $dir . DS . 'import-set.json';
        //返回
        return $res;
    }

    /**
     * 保存最后一次访问的目录ID
     * @param int $id
     * @return int ID
     */
    private function saveLastDirID($id) {
        return $this->config->save('PEX-LAST-DIR-ID', $id);
    }

    /**
     * 获取排除文件SHA1列表
     * @return array 数据数组
     */
    private function getActionExcludeSha1Files() {
        $actionName = $this->actionData[0]['name'];
        return $this->getActionValue($actionName, true);
    }

    /**
     * 获取排除文件类型列表
     * @return array 数据数组
     */
    private function getActionExcludeTypeFiles() {
        $actionName = $this->actionData[1]['name'];
        return $this->getActionValue($actionName, true);
    }

    /**
     * 添加排除文件SHA1
     * @param string $value 文件SHA1值
     * @return int 新的记录ID
     */
    public function addActionExcludeSha1($value) {
        $actionName = $this->actionData[0]['name'];
        return $this->addAction($actionName, $value);
    }

    /**
     * 添加排除文件类型
     * @param string $value 文件类型
     * @return int 新的记录ID
     */
    public function addActionExcludeType($value) {
        $actionName = $this->actionData[1]['name'];
        return $this->addAction($actionName, $value);
    }

    /**
     * 获取动作集合
     * @return array 数据数组
     */
    public function getAction() {
        return $this->actionData;
    }

    /**
     * 获取满足条件的动作
     * @param string $name 动作名称
     * @param boolean $isList 是否为非数据原型，是则处理成表方式，否则array('id','name','value')
     * @return array 数据数组
     */
    public function getActionValue($name, $isList = false) {
        $sql = 'select `id`,`name`,`value` from `action` where `name` = :name';
        $attrs = array(':name' => array($name, PDO::PARAM_STR));
        $res = $this->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
        if ($res && $isList) {
            $newRes = null;
            foreach ($res as $v) {
                $newRes[] = $v['value'];
            }
            $res = $newRes;
        }
        return $res;
    }

    /**
     * 添加新的动作记录
     * @param string $name 动作名称
     * @param string $value 数据值
     * @return int 记录ID
     */
    public function addAction($name, $value) {
        $attrs = array(':name' => array($name, PDO::PARAM_STR), ':value' => array($value, PDO::PARAM_STR));
        $sqlSearch = 'select `id` from `action` where `name` = :name and `value` = :value';
        $resSearch = $this->runSQL($sqlSearch, $attrs, 1, PDO::FETCH_ASSOC);
        if ($resSearch) {
            return $resSearch['id'];
        }
        $sql = 'insert into `action`(`id`,`name`,`value`) values(null,:name,:value)';
        $res = $this->runSQL($sql, $attrs, 4);
        return $res;
    }

    /**
     * 删除动作记录
     * @param int $id ID
     * @return boolean 是否成功
     */
    public function delAction($id) {
        $sql = 'delete from `action` where `id` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        $res = $this->runSQL($sql, $attrs, 0);
        return $res;
    }

    /**
     * 获取文件数据存储路径
     * @return string 路径
     */
    public function getDiskDataFileSrc() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'data';
        return $res;
    }

    /**
     * 获取标签模版文件路径
     * @return string 路径
     */
    private function getDiskTagsTemplateSrc() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return '';
        }
        //获取
        $res = $dir . DS . 'template-tags';
        //确保文件夹存在
        if (!CoreFile::isDir($res)) {
            if (!CoreFile::newDir($res)) {
                return '';
            }
        }
        //返回
        return $res;
    }

    /**
     * 获取所选分区数据库备份目录路径
     * @return string
     */
    private function getDiskBackupDir() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return null;
        }
        //获取
        $res = $dir . DS . 'backups';
        return $res;
    }

    /**
     * 获取备份文件分段存储路径
     * @return string
     */
    private function getDiskBackupHistoryDir() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return null;
        }
        //获取
        $res = $dir . DS . 'history-backups';
        if (!CoreFile::isDir($res)) {
            CoreFile::newDir($res);
        }
        return $res;
    }

    /**
     * 获取分区日志存储路径
     * @return string
     */
    private function getDiskLogDir() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'log';
        return $res;
    }

    /**
     * 获取所选分区数据库目录路径
     * @return string
     */
    public function getDiskDataSrc() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //检查分区文件是否存在
        if (!CoreFile::isDir($this->diskSelect['src'])) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'database';
        return $res;
    }

    /**
     * 获取数据库路径
     * @return string 数据库路径
     */
    private function getDiskDataDBSrc() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return null;
        }
        //获取
        $res = $dir . DS . 'data.sqlite';
        return $res;
    }

    /**
     * 获取标签数据库文件路径
     * @return string 数据库路径
     */
    private function getDiskDataTagSrc() {
        //检查分区
        $dir = $this->getDiskDataSrc();
        if (!$dir) {
            return null;
        }
        //获取
        $src = $dir . DS . 'tags.json';
        return $src;
    }

    /**
     * 获取等待导入目录路径
     * @return string 目录路径
     */
    private function getImportSrc() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'import';
        return $res;
    }

    /**
     * 获取文件列表和图像缓冲路径
     * @return string 目录路径
     */
    private function getDataFileCacheSrc() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'data-cache';
        return $res;
    }

    /**
     * 等待导入文件图像缓冲路径
     * @return string 目录路径
     */
    private function getImportCacheSrc() {
        //检查分区
        if (!$this->diskSelect) {
            return null;
        }
        //获取
        $res = $this->diskSelect['src'] . DS . 'import-cache';
        return $res;
    }

    /**
     * 加密字符串
     * @param string $str 需要加密的字符串
     * @return string 加密后的字符串
     */
    private function encode($str) {
        return urlencode($str);
    }

    /**
     * 解码字符串
     * @param string $str 需要解码的字符串
     * @return string 解码后的字符串
     */
    private function decode($str) {
        return urldecode($str);
    }

    /**
     * 连接数据库
     * @param string $dbSrc sqlite数据库路径
     * @return boolean 是否成功
     */
    private function connectSQLite($dbSrc) {
        if (!$this->sqlite) {
            $this->sqlite = new CoreSQLite($dbSrc);
        }
        return true;
    }

    /**
     * 转接sqlite执行sql
     * @param string $sql SQL语句
     * @param array $attrs 数据数组 eg:array(':id'=>array('value','PDO::PARAM_INT'),...)
     * @param int $resType 返回类型 0-boolean 1-fetch 2-fetchColumn 3-fetchAll 4-lastID
     * @param int $resFetch PDO-FETCH类型，如果返回fetchColumn则为列偏移值
     * @return boolean|PDOStatement 成功则返回PDOStatement句柄，失败返回false
     */
    private function runSQL($sql, $attrs = null, $resType = 0, $resFetch = null) {
        if ($this->sqlite) {
            return $this->sqlite->runSQL($sql, $attrs, $resType, $resFetch);
        }
        return false;
    }

    /**
     * 清理文件缓冲数据
     */
    private function clearFileCache() {
        $cacheDir = $this->getDataFileCacheSrc();
        $cache = new CoreCache(true, 3600, $cacheDir);
        $cache->clear();
    }

    /**
     * 清理导入文件缓冲
     */
    public function clearImportCache() {
        $dir = $this->getImportCacheSrc();
        $cache = new CoreCache(true, 3600, $dir);
        $cache->clear();
        $cache->clearImg();
    }

    /**
     * 添加日志
     * @param string $local 位置
     * @param string $message 消息
     */
    public function addLog($local, $message) {
        $this->log->add($local, $message);
    }

}

?>