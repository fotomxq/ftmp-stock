package controller

//Generic error
var err error

//Generic log
var log Log

//Generic file operate
var fileOperate FileOperate

//Generic Session
var session Session

//initialization
func init() {
	log.init()
}

//Set name
// name string - A unique string for all application identifiers
func SetName(name string){
	session.init(name)
}