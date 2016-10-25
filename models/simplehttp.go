//网络连接包
package models

import (
    "io/ioutil"
    "net/http"
    "net/url"
)

//网络通讯类构建
type simpleHttp struct{

}

//get数据
func (this *simpleHttp) get(paramUrl string, params url.Values) (res []byte, err error){
    var Url *url.URL
    Url,err = url.Parse(paramUrl)
    if err != nil{
        return nil, err
    }
    //如果参数中有中文参数,这个方法会进行URLEncode
    Url.RawQuery = params.Encode()
    resp,err := http.Get(Url.String())
    if err != nil{
        return nil, err
    }
    defer resp.Body.Close()
    return ioutil.ReadAll(resp.Body)
}

//post数据
func (this *simpleHttp) post(url string, params url.Values) (res []byte, err error){
	resp,err := http.PostForm(url, params)
    if err != nil{
        return nil ,err
    }
    defer resp.Body.Close()
    return ioutil.ReadAll(resp.Body)
}