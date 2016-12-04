package controller

import (
	"io/ioutil"
	"net"
	"net/http"
	"strings"
)

//IP address structure
type IPAddrs struct {
	addrs map[string]string
}

//Gets the IP address
func (this *IPAddrs) GetIP() map[string]string{
	this.addrs = map[string]string{
		"external" : this.GetExternal(),
		"internet" : this.GetInternal(),
	}
	return this.addrs
}

//Gets the one IP address
func (this *IPAddrs) GetOneIP() string{
	_ = this.GetIP()
	if this.addrs["external"] != ""{
		return this.addrs["external"]
	}
	if this.addrs["internal"] != ""{
		return this.addrs["internal"]
	}
	return ""
}

//Obtain an IP address from the network
func (this *IPAddrs) GetExternal() string {
	var url string = "http://myexternalip.com/raw"
	resp, err := http.Get(url)
	if err != nil {
		return "0.0.0.0"
	}
	defer resp.Body.Close()
	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return "0.0.0.0"
	}
	html := string(body)
	if err != nil {
		return "0.0.0.0"
	}
	html = strings.Replace(html, " ", "", -1)
	html = strings.Replace(html, "\n", "", -1)
	return html
}

//Get the local IP address
func (this *IPAddrs) GetInternal() string {
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
