//获取IP地址模块
package models

import (
	"net"
)

func IpAddrGetExternal() string {
	var url string = "http://myexternalip.com/raw"
	html, err := GetUrl(url)
	if err != nil {
		return "0.0.0.0"
	}
	return html
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
