//文件操作封装
package models

import (
	"io/ioutil"
	"os"
)

func init() {
}

//创建目录
func CreateDir(src string, name string) bool {
	return false
}

//读取文件
func ReadFile(src string) string {
	file, err := os.Open(src)
	if err != nil {
		panic(err)
		return ""
	}
	defer file.Close()
	c, err := ioutil.ReadAll(file)
	if err != nil {
		panic(err)
		return ""
	}
	return string(c)
}

//写入文件
func WriteFile(src string, content string) bool {
	err := ioutil.WriteFile(src, ([]byte)(content), os.ModeAppend)
	if err != nil {
		panic(err)
		return false
	}
	return true
}

//修改文件名称
func EditFileName(src string, newName string) bool {
	err := os.Rename(src, newName)
	if err != nil {
		return true
	}
	return false
}

//删除文件或文件夹
func DeleteFile(src string) bool {
	err := os.Remove(src)
	if err != nil {
		return true
	}
	return false
}

//判断是否为文件
func IsFile(src string) bool {
	return false
}

//判断是否为文件夹
func IsFolder(src string) bool {
	return false
}

//获取子文件列表
func GetFileList(src string) string {
	return ""
}

//获取文件的大小
func GetFileSize(src string) int {
	return 0
}

//获取文件夹的大小
func GetFolderSize(src string) int {
	return 0
}

//获取文件信息
func GetFileInfo(src string) bool {
	return false
}
