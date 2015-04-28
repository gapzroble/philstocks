@echo off

set GOPATH=C:\Go\gocode

go get github.com/go-martini/martini
go get github.com/go-sql-driver/mysql
go get github.com/jinzhu/gorm

rem set PATH="%PATH%;%GOPATH%\bin"
set GOPATH=%GOPATH%;%~dp0

md quotes > NUL

go build server.go
server.exe
pause
