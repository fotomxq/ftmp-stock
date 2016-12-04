package controller

import (
	"github.com/gorilla/sessions"
	"net/http"
)
//session struct
type Session struct {
	store *sessions.CookieStore
}

//initialization
func (this *Session) init(name string){
	this.store = sessions.NewCookieStore([]byte(name))
}

//Get the session data
func (this *Session) Get(w http.ResponseWriter, r *http.Request, sessionMark string) (map[interface{}]interface{}, bool) {
	s, err := this.store.Get(r, sessionMark)
	var res map[interface{}]interface{}
	if err != nil {
		log.NewLog("", err)
		return res, false
	}
	return s.Values, true
}

//Write session data
func (this *Session) Set(w http.ResponseWriter, r *http.Request, sessionMark string, data map[interface{}]interface{}) bool {
	s, err := this.store.Get(r, sessionMark)
	if err != nil {
		log.NewLog("", err)
		return false
	}
	s.Values = data
	err = s.Save(r, w)
	if err != nil {
		log.NewLog("", err)
		return false
	}
	return true
}
