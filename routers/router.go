package routers

import (
	":/Go/src/ftmp-stock/controllers"
	"github.com/astaxie/beego"
)

func init() {
    beego.Router("/", &controllers.MainController{})
}
