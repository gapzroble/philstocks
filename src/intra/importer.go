package intra

import (
	"encoding/csv"
	"fmt"
	"log"
	"net/url"
	"os"
	"path/filepath"
	"sort"
	"strconv"
	"strings"
	"time"
)

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

var (
	dropboxUrl   string
	quotesFolder string
)

func init() {
	//dropboxUrl = "https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADwhfNwFRVoQg5TaqOaVFs9a/2014?dl=1"
	dropboxUrl = "https://www.dropbox.com/sh/1dluf0lawy9a7rm/AACh8nCUuvTvP4YdVEH29On2a/2015?dl=1"
	quotesFolder = "quotes"
}

func importQuotes() {
	go func() {
		doImportQuotes()
		calculateMA()
	}()

	if ok := downloadQuotes(); ok {
		doImportQuotes()
		calculateMA()
	}
}

func doImportQuotes() {
	log.Printf("[importQuotes] started\n")
	defer log.Printf("[importQuotes] done.\n")

	pattern := quotesFolder + "\\*.csv"
	files, _ := filepath.Glob(pattern)
	sort.Sort(Files(files))

	var test CsvFile
	for _, file := range files {
		test = CsvFile{Filename: file}
		if db.Where(&test).First(&test).RecordNotFound() {
			importCsv(file)
			db.Save(test)
		}
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
	// COSCO,02/25/2015,9.72,9.75,9.52,9.66,2714500,2714433
	d, _ := time.Parse("01/02/2006", r[1])
	o, _ := strconv.ParseFloat(r[2], 64)
	h, _ := strconv.ParseFloat(r[3], 64)
	l, _ := strconv.ParseFloat(r[4], 64)
	c, _ := strconv.ParseFloat(r[5], 64)
	v, _ := strconv.ParseFloat(r[6], 64)
	n, _ := strconv.ParseFloat(r[7], 64)
	q := Quote{Symbol: r[0], Date: d, Low: l, High: h, Open: o, Close: c, Volume: v, NetBuySell: n}

	date := d.Format("2006-01-02")
	var test Quote
	if db.Where("symbol = ? and date = ?", q.Symbol, date).First(&test).RecordNotFound() {
		db.Save(&q)
		fmt.Print(".")
	} else {
		db.Model(&test).Where("symbol = ? and date = ?", q.Symbol, date).Update(&q)
	}
}

// -----------------------------------------------------------------------------

func downloadQuotes() bool {
	log.Printf("[downloadQuotes] started\n")
	defer log.Printf("[downloadQuotes] done.\n")

	target := quotesFolder + "/quotes.zip"

	// get last modified time
	file, err := os.Stat(target)
	if err == nil {
		modifiedtime := file.ModTime()
		elapsed := time.Since(modifiedtime)
		if elapsed.Hours() < 3 { // less than 3 hours
			log.Printf("[downloadQuotes] already downloaded %s ago\n", elapsed)
			return false
		}
	}

	DownloadToFile(dropboxUrl, target, "quotes")
	Unzip(target, quotesFolder)
	return true
}

func importCurrent(symbol string, qs url.Values) {
	if qs.Get("o") == "NaN" {
		return
	}
	r := make([]string, 8)
	r[0] = strings.ToUpper(symbol)
	r[1] = qs.Get("d")
	r[2] = qs.Get("o")
	r[3] = qs.Get("h")
	r[4] = qs.Get("l")
	r[5] = qs.Get("c")
	r[6] = qs.Get("v")
	r[7] = "0"
	importRow(r)
}
