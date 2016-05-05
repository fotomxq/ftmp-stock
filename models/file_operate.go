//文件操作封装
package models

import (
	"os"
)

func init() {
}

//打开一个文件
func OpenFile(src string) (*File, error) {
	return os.Open(src)
}

//创建文件
func CreateFile(src string, content string) (bool, error) {
	return false, nil
}

//创建目录
func CreateDir(src string, name string) (bool, error) {
	return false, nil
}

//读取文件
func ReadFile(src string) (string, error) {
	return "", nil
}

//写入文件
func WriteFile(src string, content string) (bool, error) {
	return false, nil
}

//写入文件，末端插入数据
func WriteFileAppend(src string, content string) (bool, error) {
	return false, nil
}

//修改文件名称
func EditFileName(src string, newName string) (bool, error) {
	return false, nil
}

//删除文件或文件夹
func DeleteFile(src string) (bool, error) {
	return false, nil
}

//判断是否为文件
func IsFile(src string) (bool, error) {
	return false, nil
}

//判断是否为文件夹
func IsFolder(src string) (bool, error) {
	return false, nil
}

//获取子文件列表
func GetFileList(src string) ([]string, error) {
	return nil, nil
}

//获取文件的大小
func GetFileSize(src string) (int, error) {
	return 0, nil
}

//获取文件夹的大小
func GetFolderSize(src string) (int, error) {
	return 0, nil
}
