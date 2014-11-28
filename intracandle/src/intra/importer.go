package intra

import (
	"encoding/csv"
	"fmt"
	"log"
	"os"
	"path/filepath"
	"sort"
	"strconv"
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
	dropboxUrl = "https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADwhfNwFRVoQg5TaqOaVFs9a/2014?dl=1"
	quotesFolder = "2014"
}

func importQuotes() {
	go doImportQuotes()
	downloadQuotes()
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
			fmt.Println("")
			db.Save(test)
		}
	}

	// force import
	go func() {
		log.Printf("[force importQuotes] started\n")
		defer log.Printf("[force importQuotes] done.\n")
		for _, file := range files {
			importCsv(file)
		}
	}()
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
		fmt.Print(".")
	}
}

// -----------------------------------------------------------------------------

func downloadQuotes() {
	target := quotesFolder + "/quotes.zip"

	// get last modified time
	file, err := os.Stat(target)
	if err == nil {
		modifiedtime := file.ModTime()
		elapsed := time.Since(modifiedtime)
		if elapsed.Hours() < 3 { // less than 3 hours
			log.Printf("[downloadQuotes] already downloaded %s ago\n", elapsed)
			return
		}
	}

	defer doImportQuotes()

	DownloadToFile(dropboxUrl, target, "quotes")
	Unzip(target, quotesFolder)
}
