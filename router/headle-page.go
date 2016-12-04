package controller

import "net/http"

/////////////////////////////////////
//This section is the page
/////////////////////////////////////

//404 error handling
func (this *Handle) page404(w http.ResponseWriter, r *http.Request) {
	if r.URL.Path == "/" {
		if this.CheckLogin(w, r) == false {
			return
		} else {
			this.ToURL(w, r, "/center")
		}
	} else {
		log.NewLog("The page can not be found,url path : "+r.URL.Path, nil)
		this.ShowTemplate(w, r, "404.html", nil)
	}
}

//Resolve the login page
func (this *Handle) pageLogin(w http.ResponseWriter, r *http.Request) {
	if this.user.CheckLogin(w, r) == true {
		this.ToURL(w, r, "/center")
		return
	} else {
		this.ShowTemplate(w, r, "login.html", nil)
		return
	}
}

//Get the site icon file
func (this *Handle) pageFavicon(w http.ResponseWriter, r *http.Request) {
	this.ToURL(w, r, "/assets/favicon.ico")
}

//Output the set page
func (this *Handle) pageSet(w http.ResponseWriter, r *http.Request) {
	if this.CheckLogin(w, r) == false {
		return
	}
	this.UpdateLanguage()
	this.ShowTemplate(w, r, "set.html", nil)
}

//Output the center page
func (this *Handle) pageCenter(w http.ResponseWriter, r *http.Request) {
	if this.CheckLogin(w, r) == false {
		return
	}
	this.UpdateLanguage()
	this.ShowTemplate(w, r, "center.html", nil)
}