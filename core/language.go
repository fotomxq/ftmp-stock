package controller

//Language configuration processor
type Language struct {
	content map[string]interface{}
	dir     string
	src     string
}

//Initialize the language configuration processor
func (this *Language) Init(languageName string) bool {
	this.dir = "." + fileOperate.GetPathSep() + "language" + fileOperate.GetPathSep()
	this.src = this.dir + languageName + ".json"
	if fileOperate.IsFile(this.src) == false {
		log.NewLog("The language configuration file does not exist.", nil)
		return false
	}
	this.content, err = LoadConfigFile(this.src)
	if err != nil {
		log.NewLog("The language configuration file could not be read properly", err)
		return false
	}
	return true
}

//Get the language
func (this *Language) Get(name string) string {
	if this.content[name] == nil {
		return ""
	}
	return this.content[name].(string)
}
