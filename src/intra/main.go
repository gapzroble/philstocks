package intra

import (
	"encoding/json"
	"github.com/go-martini/martini"
	_ "log"
	"net/http"
        "time"
        "strconv"
)

func Run() {
	go importQuotes()

	m := martini.Classic()
	m.Get("/:max/:symbol", func(params martini.Params, r *http.Request, res http.ResponseWriter) string {
		symbol := string(params["symbol"])

                // only import current if time > 9:30
                t := time.Now().Local()
                h, _ := strconv.Atoi(t.Format("15"))
                i, _ := strconv.Atoi(t.Format("04"))
                if  h > 9 || h == 9  && i >= 30 {
                    importCurrent(symbol, r.URL.Query())
                }

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
    m.RunOnAddr(":4000")
}
