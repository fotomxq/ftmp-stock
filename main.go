package main

import (
	_ "ftmpstock/models"
	_ "ftmpstock/routers"
	"github.com/astaxie/beego"
)

func main() {
	beego.Run()
}
