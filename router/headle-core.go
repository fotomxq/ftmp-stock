package controller

import (
	"net/http"
	"encoding/json"
	"html/template"
)

/////////////////////////////////////
//This part is a generic module
/////////////////////////////////////

//Get the template file path
func (this *Handle) GetTempSrc(name string) string {
	return "template" + GetPathSep() + name
}

//Output text directly to the browser
func (this *Handle) PostText(w http.ResponseWriter, r *http.Request, content string) {
	var contentByte []byte = []byte(content)
	_, err := w.Write(contentByte)
	if err != nil {
		log.NewLog("You can not directly output string data.", err)
		return
	}
}

//Jump to URL
func (this *Handle) ToURL(w http.ResponseWriter, r *http.Request, urlName string) {
	http.Redirect(w, r, urlName, http.StatusFound)
}

//Output template
func (this *Handle) ShowTemplate(w http.ResponseWriter, r *http.Request, templateFileName string, data interface{}) {
	t, err := template.ParseFiles(this.GetTempSrc(templateFileName))
	if err != nil {
		log.NewLog("The template does not output properly,template file name : "+templateFileName, err)
		return
	}
	t.Execute(w, data)
}

//Output the prompt page
func (this *Handle) showTip(w http.ResponseWriter, r *http.Request, title string, contentTitle string, content string, gotoURL string) {
	data := map[string]string{
		"title":        title,
		"contentTitle": contentTitle,
		"content":      content,
		"gotoURL":      gotoURL,
	}
	this.ShowTemplate(w, r, "tip.html", data)
}

//Common JSON processing
// w http.ResponseWriter
// r *http.Request
// data interface{} -The data to be sent
// b bool - Whether to run successfully
func (this *Handle) postJSONData(w http.ResponseWriter, r *http.Request,data interface{},b bool) {
	res := make(map[string]interface{})
	res["result"] = b
	res["data"] = data
	resJson,err := json.Marshal(res)
	if err != nil{
		log.NewLog("",err)
		this.PostText(w, r, "{'result':false,'data':''}")
	}else{
		resJsonC := string(resJson)
		this.PostText(w, r, resJsonC)
	}
}

//Check that you are logged in
func (this *Handle) CheckLogin(w http.ResponseWriter, r *http.Request) bool {
	if this.user.CheckLogin(w, r) == false {
		log.NewLog("User has not logged in, but visited the home page.", nil)
		this.ToURL(w, r, "/login")
		return false
	}
	return true
}

//Check the post data
func (this *Handle) CheckURLPost(r *http.Request) bool {
	err = r.ParseForm()
	if err != nil {
		log.NewLog("Failed to get get / post data.", err)
		return false
	}
	return true
}