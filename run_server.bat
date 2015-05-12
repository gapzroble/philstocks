@echo off

go get github.com/go-martini/martini
go get github.com/go-sql-driver/mysql
go get github.com/jinzhu/gorm

set PATH=%PATH%;%GOPATH%\bin
set GOPATH=%GOPATH%;%~dp0

md quotes > NUL

go build server.go
echo build done
rem pause
server.exe

:retry
echo retry
pause
cls
run_server
