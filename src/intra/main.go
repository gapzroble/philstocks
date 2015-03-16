package intra

import (
	"encoding/json"
	"github.com/go-martini/martini"
	_ "log"
	"net/http"
)

func Run() {
	go importQuotes()

	m := martini.Classic()
	m.Get("/:max/:symbol", func(params martini.Params, r *http.Request, res http.ResponseWriter) string {
		symbol := string(params["symbol"])
		importCurrent(symbol, r.URL.Query())

		res.Header().Set("Content-type", "application/json")
		res.Header().Set("Access-Control-Allow-Origin", "*")
		var quotes []Quote
		limit := params["max"]
		if db.Where("symbol = ?", symbol).Limit(limit).Order("date desc").Find(&quotes).RecordNotFound() {
			return "[]"
		}
		result, _ := json.Marshal(quotes)
		return string(result)
	})
	m.Run()
}
