package main

import (
	"encoding/csv"
	"encoding/json"
	"fmt"
	"github.com/go-martini/martini"
	_ "github.com/go-sql-driver/mysql"
	"github.com/jinzhu/gorm"
	"net/http"
	"os"
	"path/filepath"
	"sort"
	"strconv"
	"time"
)

type Quote struct {
	Symbol     string    `sql:"size:20;not null"`
	Date       time.Time `sql:"type:date"`
	Open       float64   `sql:"not null"`
	High       float64   `sql:"not null"`
	Low        float64   `sql:"not null"`
	Close      float64   `sql:"not null"`
	Volume     float64   `sql:"not null"`
	NetBuySell float64
}

var db *gorm.DB

func main() {
	db = initDB()
	go importQuotes()

	m := martini.Classic()
	m.Get("/quote/:symbol", func(params martini.Params, res http.ResponseWriter) string {
		res.Header().Set("Content-type", "application/json")
		res.Header().Set("Access-Control-Allow-Origin", "*")
		var quotes []Quote
		symbol := string(params["symbol"])
		fmt.Println("symbol = " + symbol)
		if db.Where("symbol = ?", symbol).Limit(5).Order("date desc").Find(&quotes).RecordNotFound() {
			return "[]"
		}
		result, _ := json.Marshal(quotes)
		return string(result)
	})
	m.Run()
}

func initDB() *gorm.DB {
	var (
		err error
		Gdb gorm.DB
	)

	Gdb, err = gorm.Open("mysql", "root:@/pse?charset=utf8&parseTime=True")
	if err != nil {
		panic(err)
	}

	err = Gdb.DB().Ping()
	if err != nil {
		panic(err)
	}

	// Gdb.LogMode(true)
	Gdb.AutoMigrate(&Quote{})
	Gdb.Model(&Quote{}).AddUniqueIndex("daily", "symbol", "date")

	return &Gdb
}

type Files []string

func (this Files) Len() int {
	return len(this)
}
func (this Files) Less(i, j int) bool {
	return this[i] > this[j]
}
func (this Files) Swap(i, j int) {
	this[i], this[j] = this[j], this[i]
}

func importQuotes() {
	// FIXME: change this
	pattern := "C:\\Users\\rroble\\Dropbox\\2014\\*.csv"
	files, _ := filepath.Glob(pattern)
	sort.Sort(Files(files))
	for _, file := range files {
		importCsv(file)
	}
}

func importCsv(filename string) {

	csvfile, err := os.Open(filename)

	if err != nil {
		fmt.Println(err)
		return
	}

	defer csvfile.Close()

	reader := csv.NewReader(csvfile)
	reader.FieldsPerRecord = -1

	rawCSVdata, err := reader.ReadAll()

	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}

	for _, row := range rawCSVdata {
		importRow(row)
	}
}

func importRow(r []string) {
	d, _ := time.Parse("01/02/2006", r[1])
	l, _ := strconv.ParseFloat(r[2], 64)
	h, _ := strconv.ParseFloat(r[3], 64)
	o, _ := strconv.ParseFloat(r[4], 64)
	c, _ := strconv.ParseFloat(r[5], 64)
	v, _ := strconv.ParseFloat(r[6], 64)
	n, _ := strconv.ParseFloat(r[7], 64)
	q := Quote{Symbol: r[0], Date: d, Low: l, High: h, Open: o, Close: c, Volume: v, NetBuySell: n}

	var test Quote
	if db.Where("symbol = ? and date = ?", q.Symbol, q.Date).First(&test).RecordNotFound() {
		db.Save(&q)
		fmt.Print(".")
	}
}
