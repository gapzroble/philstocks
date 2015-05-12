package intra

import (
	"archive/zip"
	"io"
	"io/ioutil"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strings"
)

func ReadFile(_url string) (_bytes []byte, _err error) {
	log.Printf("[ReadFile] From: %s.\n", _url)
	var res *http.Response = nil
	res, _err = http.Get(_url)
	if _err != nil {
		log.Println(_err)
		return
	}
	_bytes, _err = ioutil.ReadAll(res.Body)
	defer res.Body.Close()
	if _err != nil {
		log.Println(_err)
		return
	}
	log.Printf("[ReadFile] Size of download: %d\n", len(_bytes))
	return
}

func WriteFile(_target string, _bytes []byte) (_err error) {
	log.Printf("[WriteFile] Size of download: %d\n", len(_bytes))
	if _err = ioutil.WriteFile(_target, _bytes, 0444); _err != nil {
		log.Println(_err)
	}
	return
}

func DownloadToFile(_url string, _target string, _name string) bool {
	log.Printf("[DownloadToFile] From: %s.\n", _url)
	if bytes, err := ReadFile(_url); err == nil {
		log.Printf("%s's been downloaded.\n", _name)
		if WriteFile(_target, bytes) == nil {
			log.Printf("%s's been copied: %s\n", _name, _target)
			return true
		}
	}
	return false
}

func Unzip(src, dest string) error {
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
				log.Println(err)
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
