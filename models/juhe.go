package main
import (
    "io/ioutil"
    "net/http"
    "net/url"
    "fmt"
    "encoding/json"
)
 
//----------------------------------
// 股票数据调用示例代码 － 聚合数据
// 在线接口文档：http://www.juhe.cn/docs/21
//----------------------------------
 
const APPKEY = "*******************" //您申请的APPKEY
 
func main(){
 
    //1.沪深股市
    Request1()
 
    //2.香港股市
    Request2()
 
    //3.美国股市
    Request3()
 
    //4.香港股市列表
    Request4()
 
    //5.美国股市列表
    Request5()
 
    //6.深圳股市列表
    Request6()
 
    //7.沪股列表
    Request7()
 
}
 
//1.沪深股市
func Request1(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/hs"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("gid","") //股票编号，上海股市以sh开头，深圳股市以sz开头如：sh601009
    param.Set("key",APPKEY) //APP Key
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//2.香港股市
func Request2(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/hk"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("num","") //股票代码，如：00001 为“长江实业”股票代码
    param.Set("key",APPKEY) //APP Key
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//3.美国股市
func Request3(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/usa"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("gid","") //股票代码，如：aapl 为“苹果公司”的股票代码
    param.Set("key",APPKEY) //APP Key
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//4.香港股市列表
func Request4(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/hkall"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("key",APPKEY) //您申请的APPKEY
    param.Set("page","") //第几页,每页20条数据,默认第1页
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//5.美国股市列表
func Request5(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/usaall"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("key",APPKEY) //您申请的APPKEY
    param.Set("page","") //第几页,每页20条数据,默认第1页
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//6.深圳股市列表
func Request6(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/szall"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("key",APPKEY) //您申请的APPKEY
    param.Set("page","") //第几页(每页20条数据),默认第1页
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
//7.沪股列表
func Request7(){
    //请求地址
    juheURL :="http://web.juhe.cn:8080/finance/stock/shall"
 
    //初始化参数
    param:=url.Values{}
 
    //配置请求参数,方法内部已处理urlencode问题,中文参数可以直接传参
    param.Set("key",APPKEY) //您申请的APPKEY
    param.Set("page","") //第几页,每页20条数据,默认第1页
 
 
    //发送请求
    data,err:=Get(juheURL,param)
    if err!=nil{
        fmt.Errorf("请求失败,错误信息:\r\n%v",err)
    }else{
        var netReturn map[string]interface{}
        json.Unmarshal(data,&netReturn)
        if netReturn["error_code"].(float64)==0{
            fmt.Printf("接口返回result字段是:\r\n%v",netReturn["result"])
        }
    }
}
 
 
 
// get 网络请求
func Get(apiURL string,params url.Values)(rs[]byte ,err error){
    var Url *url.URL
    Url,err=url.Parse(apiURL)
    if err!=nil{
        fmt.Printf("解析url错误:\r\n%v",err)
        return nil,err
    }
    //如果参数中有中文参数,这个方法会进行URLEncode
    Url.RawQuery=params.Encode()
    resp,err:=http.Get(Url.String())
    if err!=nil{
        fmt.Println("err:",err)
        return nil,err
    }
    defer resp.Body.Close()
    return ioutil.ReadAll(resp.Body)
}
 
// post 网络请求 ,params 是url.Values类型
func Post(apiURL string, params url.Values)(rs[]byte,err error){
    resp,err:=http.PostForm(apiURL, params)
    if err!=nil{
        return nil ,err
    }
    defer resp.Body.Close()
    return ioutil.ReadAll(resp.Body)
}