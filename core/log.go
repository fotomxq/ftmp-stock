package controller

import (
	"fmt"
	"time"
	"os"
)

//log struct
//You need to set the related configuration.
//IP address if the output is set, otherwise you can leave empty.
type Log struct {
	logDirSrc         string
	isSendErrorToFmt  bool
	isSendMsgToFmt    bool
	isSendErrorToFile bool
	isSendMsgToFile   bool
	isAppendTime      bool
	isAppendIP        bool
	ip                string
	ipAddrs IPAddrs
	oneFileName string
	isForward bool
	lastSrc string
}

//Initialize the configuration
//This function must be executed before using Log.
func (this *Log) init() {
	this.logDirSrc = "./log"
	this.isSendErrorToFmt = true
	this.isSendMsgToFmt = true
	this.isSendErrorToFile = true
	this.isSendMsgToFile = true
	this.isAppendTime = true
	this.isAppendIP = true
	this.oneFileName = ""
	this.isForward = false
}

//Set log dir src
func (this *Log) SetLogDirSrc(logDirSrc string){
	this.logDirSrc = logDirSrc
}

//Set is send error to fmt
func (this *Log) SetIsSendErrorToFmt(b bool){
	this.isSendErrorToFmt = b
}

//Set is send msg to fmt
func (this *Log) SetIsSendMsgToFmt(b bool){
	this.isSendMsgToFmt = b
}

//Set is send error to file
func (this *Log) SetIsSendErrorToFile(b bool){
	this.isSendErrorToFile = b
}

//Set is send error to file
func (this *Log) SetIsSendMsgToFile(b bool){
	this.isSendMsgToFile = b
}

//Setis append time
func (this *Log) SetIsAppendTime(b bool){
	this.isAppendTime = b
}

//Set is append ip
func (this *Log) SetIsAppendIP(b bool){
	this.isAppendIP = b
}

//Set one file name
func (this *Log) SetOneFileName(name string) {
	this.oneFileName = name
}

//Set is forward
func (this *Log) SetIsForward(b bool) {
	this.isForward = b
}

//New log
//The log is automatically sent according to the settings.
func (this *Log) NewLog(msg string, err error) {
	if this.isAppendIP == true {
		this.UpdateIP()
	}
	if msg != "" {
		if this.isSendMsgToFmt == true {
			this.SendFmtPrintln(msg)
		}
		if this.isSendMsgToFile == true {
			this.SendFile(msg)
		}
	}
	if err != nil {
		if this.isSendErrorToFmt == true {
			this.SendFmtPrintln("Error : " + err.Error())
		}
		if this.isSendErrorToFile == true {
			this.SendFile("Error : " + err.Error())
		}
	}

}

//Update the IP address
func (this *Log) UpdateIP() {
	this.ip = this.ipAddrs.GetOneIP()
}

//Send logs to the console
func (this *Log) SendFmtPrintln(msg string) {
	if this.isAppendTime == true {
		msg = this.GetNowTime() + " " + this.ip + " " + msg
	}
	fmt.Println(msg)
}

//Send log to file
func (this *Log) SendFile(content string) {
	if this.logDirSrc == "" {
		this.SendFmtPrintln("The log directory path is not provided.")
		return
	}
	if this.isAppendTime == true {
		content = this.GetNowTime() + " " + this.ip + " " + content + "\n"
	}
	var src string
	if this.oneFileName != ""{
		err = os.MkdirAll(this.logDirSrc, os.ModePerm)
		if err != nil{
			this.SendFmtPrintln(err.Error())
			return
		}
		src = this.logDirSrc + fileOperate.GetPathSep() + this.oneFileName + ".log"
	}else{
		src, err = fileOperate.GetTimeDirSrc(this.logDirSrc, ".log")
		if err != nil {
			this.SendFmtPrintln("Unable to create log save directory path.")
			return
		}
	}
	contentByte := []byte(content)
	err = fileOperate.WriteFileAppend(src, contentByte, this.isForward)
	if err != nil{
		this.SendFmtPrintln(err.Error())
	}
	this.lastSrc = src
}

//Gets the current time
func (this *Log) GetNowTime() string {
	return time.Now().String()
}