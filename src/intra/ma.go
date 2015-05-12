package intra

import (
	"log"
)

func calculateMA() {
	return
	// log.Printf("[calculateMA] started\n")
	// defer log.Printf("[calculateMA] done.\n")

	rows, err := db.Table("quotes").Select("distinct symbol").Order("date asc").Rows()
	defer rows.Close()
	if err != nil {
		log.Fatal(err)
	}

	var symbol string
	for rows.Next() {
		rows.Scan(&symbol)
		//log.Println(symbol)
		calculateMAForSymbol(symbol)
		break
	}
}

func calculateMAForSymbol(symbol string) {
	var quotes []Quote
	if db.Where("symbol = ?", symbol).Order("date desc").Find(&quotes).RecordNotFound() {
		return
	}

	// var mas []MovingAverage

	//count := len(quotes)
	//for i, _ := range quotes {
	//log.Printf("%d: (%d) %d-%d\n", i, count, count-i, count-i-20)
	// if quotes[count-20-i] {
	// 	log.Println("here")
	// }
	// log.Printf("%d: %s %s\n", i, quote.Date, quote.Symbol)
	// if (i+1)%20 == 0 {
	// 	log.Println("calculate 20 ma")
	// }
	//}
}
