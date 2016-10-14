//文件操作封装
package models

//没有完成的部分有：获取子文件列表

import (
	"io/ioutil"
	"os"
)

//文件类结构
type fileOperate struct{
}

//创建目录
func (this *fileOperate) CreateDir(src string) bool {
	err := os.MkdirAll(src,os.ModePerm)
	if err != nil{
		return false
	}
	return true
}

//读取文件
func (this *fileOperate) ReadFile(src string) string {
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
func (this *fileOperate) WriteFile(src string, content string) bool {
	err := ioutil.WriteFile(src, ([]byte)(content), os.ModeAppend)
	if err != nil {
		panic(err)
		return false
	}
	return true
}

//修改文件名称
func (this *fileOperate) EditFileName(src string, newName string) bool {
	err := os.Rename(src, newName)
	if err != nil {
		return true
	}
	return false
}

//删除文件或文件夹
func (this *fileOperate) DeleteFile(src string) bool {
	err := os.RemoveAll(src)
	if err != nil {
		return true
	}
	return false
}

//判断路径是否存在
func (this *fileOperate) IsExist(src string) bool{
	_, err := os.Stat(src)
	return err == nil || os.IsExist(err)
}

//判断是否为文件
func (this *fileOperate) IsFile(src string) bool {
	info, err := os.Stat(src)
	if err != nil{
		panic(err)
		return false
	}
	return !info.IsDir()
}

//判断是否为文件夹
func (this *fileOperate) IsFolder(src string) bool {
	info, err := os.Stat(src)
	if err != nil{
		panic(err)
		return false
	}
	return info.IsDir()
}

//获取子文件列表
func (this *fileOperate) GetFileList(src string) string {
	return ""
}

//获取文件的大小
func (this *fileOperate) GetFileSize(src string) int64 {
	info, err := os.Stat(src)
	if err != nil{
		return 0
	}
	return info.Size()
}

//获取文件信息
func (this *fileOperate) GetFileInfo(src string) (os.FileInfo ,error) {
	info, err := os.Stat(src)
	return info,err
}
