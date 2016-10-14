//文件操作模块
package models

import (
	"io/ioutil"
	"os"
)

//文件类结构
type fileOperate struct{
}

func (this *fileOperate) CreateDir()