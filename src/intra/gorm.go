package intra

import (
	_ "github.com/go-sql-driver/mysql"
	"github.com/jinzhu/gorm"
	"time"
)

type Quote struct {
	Symbol     string    `sql:"size:20;not null" json:"-"`
	Date       time.Time `sql:"type:date;not null"`
	Open       float64   `sql:"not null"`
	High       float64   `sql:"not null"`
	Low        float64   `sql:"not null"`
	Close      float64   `sql:"not null"`
	Volume     float64   `sql:"not null"`
	NetBuySell float64   `json:"-"`
}

type CsvFile struct {
	Filename string
}

type MovingAverage struct {
	Symbol string    `sql:"size:20;not null" json:"-"`
	Date   time.Time `sql:"type:date;not null"`
	Period int       `sql:"not null"`
	Value  float64   `sql:"not null"`
	Point  string    `sql:"size:5;not null"` // low,high,open,close
}

var db *gorm.DB

func init() {
	// FIXME: change this
	db = initDB("mysql", "root:testing@/pse?charset=utf8&parseTime=True")
}

func initDB(driver, dsn string) *gorm.DB {
	var (
		err error
		Gdb gorm.DB
	)

	Gdb, err = gorm.Open(driver, dsn)
	if err != nil {
		panic(err)
	}

	err = Gdb.DB().Ping()
	if err != nil {
		panic(err)
	}

	Gdb.LogMode(false)
	Gdb.AutoMigrate(&Quote{}).AddUniqueIndex("daily", "symbol", "date")
	Gdb.AutoMigrate(&CsvFile{}).AddUniqueIndex("file", "filename")
	Gdb.AutoMigrate(&MovingAverage{}).AddUniqueIndex("ma", "symbol", "date", "point")

	return &Gdb
}
