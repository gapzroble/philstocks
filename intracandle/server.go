package main

import (
	"archive/zip"
	"encoding/csv"
	"encoding/json"
	"github.com/go-martini/martini"
	_ "github.com/go-sql-driver/mysql"
	"github.com/jinzhu/gorm"
	"io"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"sort"
	"strconv"
	"strings"
	"time"
)

type Quote struct {
	Symbol     string    `sql:"size:20;not null" json:"-"`
	Date       time.Time `sql:"type:date"`
	Open       float64   `sql:"not null"`
	High       float64   `sql:"not null"`
	Low        float64   `sql:"not null"`
	Close      float64   `sql:"not null"`
	Volume     float64   `sql:"not null"`
	NetBuySell float64   `json:"-"`
}

var db *gorm.DB

func main() {
	db = initDB()
	go importQuotes()

	m := martini.Classic()
	m.Get("/:max/:symbol", func(params martini.Params, res http.ResponseWriter) string {
		res.Header().Set("Content-type", "application/json")
		res.Header().Set("Access-Control-Allow-Origin", "*")
		var quotes []Quote
		symbol := string(params["symbol"])
		limit := params["max"]
		if db.Where("symbol = ?", symbol).Limit(limit).Order("date desc").Find(&quotes).RecordNotFound() {
			return "[]"
		}
		result, _ := json.Marshal(quotes)
		return string(result)
	})
	m.Run()
}

// -----------------------------------------------------------------------------

func initDB() *gorm.DB {
	var (
		err error
		Gdb gorm.DB
	)

	// FIXME: change this
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

// -----------------------------------------------------------------------------

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
	go doImportQuotes()
	downloadQuotes()
}

func doImportQuotes() {
	log.Printf("[importQuotes] init\n")
	defer log.Printf("[importQuotes] done.\n")
	pattern := "2014\\*.csv"
	files, _ := filepath.Glob(pattern)
	sort.Sort(Files(files))
	for _, file := range files {
		importCsv(file)
	}
}

func importCsv(filename string) {

	csvfile, err := os.Open(filename)

	if err != nil {
		log.Println(err)
		return
	}

	defer csvfile.Close()

	reader := csv.NewReader(csvfile)
	reader.FieldsPerRecord = -1

	rawCSVdata, err := reader.ReadAll()

	if err != nil {
		log.Println(err)
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
		log.Print(".")
	}
}

// -----------------------------------------------------------------------------

func downloadQuotes() {
	target := "2014/target.zip"

	// get last modified time
	file, err := os.Stat(target)
	if err == nil {
		modifiedtime := file.ModTime()
		elapsed := time.Since(modifiedtime)
		if elapsed.Hours() < 3 { // less than 3 hours
			log.Printf("[downloadQuotes] already downloaded %s ago, abort.", elapsed)
			return
		}
	}

	defer doImportQuotes()

	DownloadToFile("https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADwhfNwFRVoQg5TaqOaVFs9a/2014?dl=1", target, "quotes")
	unzip(target, "2014")
}

func ReadFile(_url string) (_bytes []byte, _err error) {
	log.Printf("[ReadFile] From: %s.\n", _url)
	var res *http.Response = nil
	res, _err = http.Get(_url)
	if _err != nil {
		log.Fatal(_err)
	}
	_bytes, _err = ioutil.ReadAll(res.Body)
	defer res.Body.Close()
	if _err != nil {
		log.Fatal(_err)
	}
	log.Printf("[ReadFile] Size of download: %d\n", len(_bytes))
	return
}

func WriteFile(_target string, _bytes []byte) (_err error) {
	log.Printf("[WriteFile] Size of download: %d\n", len(_bytes))
	if _err = ioutil.WriteFile(_target, _bytes, 0444); _err != nil {
		log.Fatal(_err)
	}
	return
}

func DownloadToFile(_url string, _target string, _name string) {
	log.Printf("[DownloadToFile] From: %s.\n", _url)
	if bytes, err := ReadFile(_url); err == nil {
		log.Printf("%s's been downloaded.\n", _name)
		if WriteFile(_target, bytes) == nil {
			log.Printf("%s's been copied: %s\n", _name, _target)
		}
	}
}

func unzip(src, dest string) error {
	r, err := zip.OpenReader(src)
	if err != nil {
		return err
	}
	defer r.Close()

	for _, f := range r.File {
		rc, err := f.Open()
		if err != nil {
			return err
		}
		defer rc.Close()

		fpath := filepath.Join(dest, f.Name)
		if f.FileInfo().IsDir() {
			os.MkdirAll(fpath, f.Mode())
		} else {
			var fdir string
			if lastIndex := strings.LastIndex(fpath, string(os.PathSeparator)); lastIndex > -1 {
				fdir = fpath[:lastIndex]
			}

			err = os.MkdirAll(fdir, f.Mode())
			if err != nil {
				log.Fatal(err)
				return err
			}
			f, err := os.OpenFile(
				fpath, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, f.Mode())
			if err != nil {
				return err
			}
			defer f.Close()

			_, err = io.Copy(f, rc)
			if err != nil {
				return err
			}
		}
	}
	return nil
}
