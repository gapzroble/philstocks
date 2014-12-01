@echo off

go get github.com/go-martini/martini
go get github.com/go-sql-driver/mysql
go get github.com/jinzhu/gorm

set PATH=%PATH%;%GOPATH%\bin
set GOPATH=%GOPATH%;%~dp0

go build server.go
server.exe
