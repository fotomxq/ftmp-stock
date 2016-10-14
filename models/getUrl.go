//从HTML获取数据
package models

import (
	"io/ioutil"
	"net/http"
    "net/url"
)

func GetUrl(url string) (string, error) {
	resp, err := http.Get(url)
	if err != nil {
		return "", err
	}
	defer resp.Body.Close()
	body, err2 := ioutil.ReadAll(resp.Body)
	if err2 != nil {
		return "", err2
	}
	return string(body), nil
}