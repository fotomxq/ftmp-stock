//获取IP地址模块
package models

import (
	"net"
	"io/ioutil"
	"net/http"
)

func IpAddrGetExternal() (res string,err error) {
	var url string = "http://myexternalip.com/raw"
	resp, err := http.Get(url)
	if err != nil {
		return "0.0.0.0", err
	}
	defer resp.Body.Close()
	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return "0.0.0.0", err
	}
	html, err := string(body), nil
	if err != nil {
		return "0.0.0.0", err
	}
	return html,nil
}

//获取本机IP地址
func IpAddrGetInternal() string {
	addrs, err := net.InterfaceAddrs()
	if err != nil {
		return "0.0.0.0"
	}
	for _, a := range addrs {
		if ipnet, ok := a.(*net.IPNet); ok && !ipnet.IP.IsLoopback() {
			if ipnet.IP.To4() != nil {
				return ipnet.IP.String()
			}
		}
	}
	return "0.0.0.0"
}
