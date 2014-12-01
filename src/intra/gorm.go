package intra

import (
	_ "github.com/go-sql-driver/mysql"
	"github.com/jinzhu/gorm"
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

type CsvFile struct {
	Filename string
}

var (
	db  *gorm.DB
	dsn string
)

func init() {
	dsn = "root:@/pse?charset=utf8&parseTime=True"
	db = initDB()
}

func initDB() *gorm.DB {
	var (
		err error
		Gdb gorm.DB
	)

	// FIXME: change this
	Gdb, err = gorm.Open("mysql", dsn)
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

	return &Gdb
}
