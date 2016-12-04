package controller

import (
	"net/http"
)

//The page handle
type Handle struct {
	//User processor
	user User
	//language configuration processor
	lang Language
	//Database processor
	db *Database
}

func (this *Handle) Init(db *Database) {
	//Save the database processor
	this.db = db
	//Initialize the user processor
	this.user.Init(db, 3600)
}

/////////////////////////////////////
//This section is the feedback page
/////////////////////////////////////

//Submit data Try to log in
func (this *Handle) actionLogin(w http.ResponseWriter, r *http.Request) {
	postUser := r.FormValue("email")
	postPasswd := r.FormValue("password")
	b := this.user.LoginIn(w, r, postUser, postPasswd)
	if b == false {
		this.ToURL(w, r, "/login")
		return
	} else {
		this.ToURL(w, r, "/center")
	}
}

//sign out
func (this *Handle) actionLogout(w http.ResponseWriter, r *http.Request) {
	if this.user.CheckLogin(w, r) == false {
		this.ToURL(w, r, "/login")
		return
	}
	b := this.user.Logout(w,r)
	if b == false{
		//...
	}
	this.showTip(w, r, this.lang.Get("handle-logout-title"), this.lang.Get("handle-logout-contentTitle"), this.lang.Get("handle-logout-content"), "/login")
}

//Resolution settings page
func (this *Handle) actionSet(w http.ResponseWriter, r *http.Request) {
	//If not, jump
	if this.CheckLogin(w, r) == false {
		return
	}
	//Make sure that post / get is fine
	b := this.CheckURLPost(r)
	if b == false {
		return
	}
	//Gets the submit action type
	postAction := r.FormValue("action")
	switch postAction {
	case "coll":
		postName := r.FormValue("name")
		if postName == ""{
			return
		}
		if postName == "run-all" {
			coll.Run("")
		}else{
			coll.Run(postName)
		}
		this.postJSONData(w,r,"",true)
		break
	case "get-status":
		data,b := coll.GetStatus()
		this.postJSONData(w,r,data,b)
		break
	case "clear":
		postName := r.FormValue("name")
		if postName == ""{
			return
		}
		this.postJSONData(w,r,coll.ClearColl(postName),true)
		break
	case "close":
		postName := r.FormValue("name")
		if postName == ""{
			return
		}
		this.postJSONData(w,r,coll.ChangeStatus(postName,false),true)
		break
	default:
		this.page404(w, r)
		return
		break
	}
}

//Feedback center action
func (this *Handle) actionCenter(w http.ResponseWriter, r *http.Request) {
	if this.CheckLogin(w, r) == false {
		return
	}
	this.UpdateLanguage()
}

//Feedback center view content action
func (this *Handle) actionView(w http.ResponseWriter, r *http.Request) {
	if this.CheckLogin(w, r) == false {
		return
	}
	this.UpdateLanguage()
}

//debug
func (this *Handle) actionDebug(w http.ResponseWriter, r *http.Request) {
	if configData["debug"] != "true"{
		this.page404(w,r)
		return
	}
	//If not, jump
	if this.CheckLogin(w, r) == false {
		return
	}
	//Make sure that post / get is fine
	b := this.CheckURLPost(r)
	if b == false {
		return
	}
	//Gets the submit action type
	postAction := r.FormValue("action")
	switch postAction {
	case "coll":
		postName := r.FormValue("name")
		if postName == "" {
			return
		}
		if postName == "run-all" {
			coll.Run("")
		} else {
			coll.Run(postName)
		}
		this.postJSONData(w, r, "", true)
		break
	default:
		break
	}
}