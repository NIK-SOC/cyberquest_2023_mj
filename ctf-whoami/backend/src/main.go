package main

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"io"
	"math/rand"
	"net/http"
	"os"
	"path"
	"path/filepath"
	"strings"
	"time"

	"github.com/GRbit/go-pcre"
)

type ImageHash struct {
	Path string
	Hash string
}

const dogImagesPath = "../assets/dogs"
const catImagesPath = "../assets/cats"
const frontendPath = "../frontend"
const apiKey = "cq23{n3ver_p4rS6_URL5_w1th_r3g3x_1066f6cec9dd3d4274940c52a8d1b3d0}"

// (?:([^\:]*)\:\/\/)?(?:([^\:\@]*)(?:\:([^\@]*))?\@)?(?:([^\/\:]*))?(?:\:([0-9]*))?\/(\/[^\?#]*(?=.*?\/)\/)?([^\?#]*)?(?:\?([^#]*))?(?:#(.*))?
const urlRegex = `^(?:([^:/?#.]+):)?(?:\/\/(?:([^/?#]*)@)?([^/#?]*?)(?::([0-9]+))?(?=[/#?]|$))?([^?#]+)?(?:\?([^#]*))?(?:#([\s\S]*))?$`

var dogImageHashes []ImageHash
var catImageHashes []ImageHash

func logRequestHandler(h http.Handler) http.Handler {
	fn := func(w http.ResponseWriter, r *http.Request) {

		h.ServeHTTP(w, r)

		uri := r.URL.String()
		method := r.Method
		remoteAddr := r.RemoteAddr
		userAgent := r.UserAgent()
		date := time.Now().Format("2006-01-02 15:04:05")

		if method == http.MethodOptions {
			return
		}
		if r.Header.Get("X-From-Server") == "true" {
			return
		}
		if strings.HasPrefix(uri, "/classify") || strings.HasPrefix(uri, "/image") || strings.HasPrefix(uri, "/proxy") || uri == "/" {
			headers := "Headers: "
			for key, value := range r.Header {
				if key == "Cookie" || key == "User-Agent" || key == "Accept-Encoding" || key == "Connection" || key == "Accept-Language" || key == "Accept" {
					continue
				}
				headers += key + ": " + value[0] + ", "
			}
			headers = headers[:len(headers)-2]
			fmt.Printf("[%s] %s %s %s %s %s\n", date, remoteAddr, method, uri, userAgent, headers)
			return
		}
	}

	return http.HandlerFunc(fn)
}

func main() {
	readAndStoreImageHashes(dogImagesPath, &dogImageHashes)
	readAndStoreImageHashes(catImagesPath, &catImageHashes)

	httpPort := getPort("BACKEND_PORT", "53499")
	proxyPort := getPort("PROXY_PORT", "25998")

	httpRouter := http.NewServeMux()
	proxyRouter := http.NewServeMux()

	proxyRouter.HandleFunc("/", http.HandlerFunc(notFoundHandler))

	httpRouter.HandleFunc("/classify", classifyImage)
	httpRouter.HandleFunc("/image", getRandomImage)
	httpRouter.Handle("/", customNotFound(http.Dir(frontendPath)))
	proxyRouter.HandleFunc("/proxy", proxyHandler)

	go startServer(httpPort, httpRouter)
	startServer(proxyPort, proxyRouter)
}

func getPort(envVar, defaultPort string) string {
	port := os.Getenv(envVar)
	if port == "" {
		return defaultPort
	}
	return port
}

func notFoundHandler(w http.ResponseWriter, r *http.Request) {
	w.WriteHeader(http.StatusNotFound)
}

func customNotFound(fs http.FileSystem) http.Handler {
	fileServer := http.FileServer(fs)
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		_, err := fs.Open(path.Clean(r.URL.Path))
		if os.IsNotExist(err) {
			notFoundHandler(w, r)
			return
		}
		fileServer.ServeHTTP(w, r)
	})
}

func startServer(port string, router http.Handler) {
	httpPortStr := ":" + port
	fmt.Println("Listening on port " + port)
	err := http.ListenAndServe(httpPortStr, logRequestHandler(router))
	if err != nil {
		fmt.Println("Error starting server on port " + port + ": " + err.Error())
	}
}

func addCorsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS, GET, HEAD")
	w.Header().Set("Access-Control-Allow-Headers", "Accept, Content-Type, Content-Length, Accept-Encoding, Authorization")
}

func addProxyCorsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS, GET, HEAD")
	w.Header().Set("Access-Control-Allow-Headers", "Accept, Content-Type, Content-Length, Accept-Encoding, X-Playground")
}

func readAndStoreImageHashes(path string, hashes *[]ImageHash) {
	files, err := filepath.Glob(filepath.Join(path, "*.jpeg"))
	if err != nil {
		panic(err)
	}

	for _, file := range files {
		hash, err := calculateMD5(file)
		if err != nil {
			panic(err)
		}
		*hashes = append(*hashes, ImageHash{Path: file, Hash: hash})
	}
}

func calculateMD5(filePath string) (string, error) {
	file, err := os.Open(filePath)
	if err != nil {
		return "", err
	}
	defer file.Close()

	hasher := md5.New()
	_, err = io.Copy(hasher, file)
	if err != nil {
		return "", err
	}

	return hex.EncodeToString(hasher.Sum(nil)), nil
}

func proxyHandler(w http.ResponseWriter, r *http.Request) {
	addProxyCorsHeaders(w)
	if r.Method == "OPTIONS" {
		return
	}
	if r.Header.Get("X-Playground") != "true" {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}
	url := r.URL.Query().Get("url")
	if url == "" {
		http.Error(w, "Missing url query parameter", http.StatusBadRequest)
		return
	}
	regexp, err := pcre.Compile(urlRegex, 0)
	if err != nil {
		println(err.Error())
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}
	if !regexp.MatchStringWFlags(url, 0) || regexp.NewMatcherString(url, 0).GroupString(3) != "whoami.honeylab.hu" {
		http.Error(w, "Invalid url", http.StatusBadRequest)
		return
	}
	url = strings.Replace(url, "whoami.honeylab.hu", "localhost:"+os.Getenv("BACKEND_PORT"), 1)
	// if we find a backslash in the url cut away everything after it including the backslash
	// ugly way to implement this, but Go is too safe and doesn't allow backslashes in urls
	// and I wanted this challenge to scale well
	if strings.Contains(url, "\\") {
		url = url[:strings.Index(url, "\\")]
	}
	print("Proxying request to " + url + "\n")
	req, err := http.NewRequest(r.Method, url, r.Body)
	if err != nil {
		print(err.Error() + "\n")
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}
	for key, value := range r.Header {
		if key == "Host" {
			continue
		}
		req.Header.Set(key, value[0])
	}
	req.Header.Set("Authorization", "Bearer "+apiKey)
	req.Header.Set("X-From-Server", "true")
	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}
	for key, value := range resp.Header {
		w.Header().Set(key, value[0])
	}
	w.WriteHeader(resp.StatusCode)
	io.Copy(w, resp.Body)

	defer resp.Body.Close()
}

func classifyImage(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	if r.Method == "OPTIONS" {
		return
	}
	if !strings.EqualFold(r.Header.Get("Authorization"), "Bearer "+apiKey) {
		http.Error(w, "Wrong API key", http.StatusUnauthorized)
		return
	}
	r.Body = http.MaxBytesReader(w, r.Body, 10<<20)
	err := r.ParseMultipartForm(10 << 20)
	if err != nil {
		http.Error(w, "File too large or no multipart data", http.StatusBadRequest)
		return
	}
	file, _, err := r.FormFile("image")
	if err != nil {
		if err.Error() == "http: no such file" {
			http.Error(w, "No image uploaded", http.StatusBadRequest)
			return
		}
		http.Error(w, "Image processing failed", http.StatusInternalServerError)
		return
	}
	defer file.Close()

	userHash, err := calculateMD5FromReader(file)
	if err != nil {
		http.Error(w, "Image processing failed", http.StatusInternalServerError)
		return
	}

	for _, hash := range dogImageHashes {
		if userHash == hash.Hash {
			w.Write([]byte("dog"))
			return
		}
	}
	for _, hash := range catImageHashes {
		if userHash == hash.Hash {
			w.Write([]byte("cat"))
			return
		}
	}

	w.Write([]byte("unclassified"))
}

func calculateMD5FromReader(r io.Reader) (string, error) {
	hasher := md5.New()
	_, err := io.Copy(hasher, r)
	if err != nil {
		return "", err
	}
	return hex.EncodeToString(hasher.Sum(nil)), nil
}

func getRandomImage(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	w.Header().Set("Cache-Control", "no-cache, no-store, must-revalidate")
	var image ImageHash
	if rand.Intn(2) == 0 {
		image = getRandomImageFromFolder(dogImagesPath)
	} else {
		image = getRandomImageFromFolder(catImagesPath)
	}

	http.ServeFile(w, r, image.Path)
}

func getRandomImageFromFolder(folderPath string) ImageHash {
	files, err := filepath.Glob(filepath.Join(folderPath, "*.jpeg"))
	if err != nil {
		return ImageHash{Path: "", Hash: ""}
	}

	randomIndex := rand.Intn(len(files))
	hash, err := calculateMD5(files[randomIndex])
	if err != nil {
		return ImageHash{Path: "", Hash: ""}
	}

	return ImageHash{Path: files[randomIndex], Hash: hash}
}
