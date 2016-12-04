package controller

import (
	"encoding/json"
	"os"
	"io/ioutil"
)

//Read the configuration file
func LoadConfigFile(configSrc string) (map[string]interface{}, error) {
	var result map[string]interface{}
	fd, err := os.Open(configSrc)
	if err != nil {
		return result, err
	}
	defer fd.Close()
	content, err := ioutil.ReadAll(fd)
	if err != nil {
		return result, err
	}
	if err != nil {
		return result, err
	}
	err = json.Unmarshal(content, &result)
	return result, err
}

//Write the configuration file
func SaveConfigFile(configSrc string, configData interface{}) error {
	contentJson, err := json.Marshal(configData)
	if err != nil {
		return err
	}
	return ioutil.WriteFile(configSrc, contentJson, os.ModeAppend)
}
