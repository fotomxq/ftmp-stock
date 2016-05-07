//文件操作封装
package models

import (
	"fmt"
	"io/ioutil"
	"os"
	"strings"
)

func init() {
	src := "c:/c.txt"
	fmt.Println(ReadFile(src))
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
	read := strings.NewReader(content)
	newC, errNewC := read.ReadByte()
	if errNewC != nil {
		panic(errNewC)
		return false
	}
	errWriteFile := ioutil.WriteFile(src, newC)
	if errWriteFile != nil {
		panic(errWriteFile)
		return false
	}
	return true
}

//修改文件名称
func EditFileName(src string, newName string) bool {
	return false
}

//删除文件或文件夹
func DeleteFile(src string) bool {
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
