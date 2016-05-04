//文件操作封装
package models

func init() {

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

func WriteFileAppend(src string, content string) (bool, error) {
	return false, nil
}
