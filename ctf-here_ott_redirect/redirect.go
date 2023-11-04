package main

import (
	"crypto/tls"
	"fmt"
	"math/rand"
	"net"
	"net/http"
	"os"
	"strings"
	"sync"
	"time"
)

// Define backend servers.
var backendServers = []string{
	"https://10.10.1.11",
	"https://10.10.2.11",
	"https://10.10.3.11",
	"https://10.10.4.11",
	"https://10.10.5.11",
	"https://10.10.6.11",
	"https://10.10.7.11",
	"https://10.10.8.11",
	"https://10.10.9.11",
	// Add more backend servers as needed
}

var acceptedDomain = "hereott.honeylab.hu"

var sessionMutex sync.Mutex

var sessions = make(map[string]string)

func addCorsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Headers", "Authorization, UUID, UID, SIG, SerialNumber, Content-Type")
	w.Header().Set("Access-Control-Allow-Methods", "GET, POST, DELETE, OPTIONS, HEAD")
}

func main() {
	tlsConfig := &tls.Config{}

	port := os.Getenv("BACKEND_PORT")
	if port == "" {
		port = "8443"
	}

	acceptedDomain = acceptedDomain + ":" + port

	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		if r.Host != acceptedDomain {
			w.WriteHeader(http.StatusNoContent)
			return
		}
		if r.Method == "OPTIONS" {
			addCorsHeaders(w)
			w.WriteHeader(http.StatusNoContent)
			return
		}

		clientID := getClientID(r)
		sessionMutex.Lock()
		selectedBackend, exists := sessions[clientID]
		sessionMutex.Unlock()

		if !exists {
			randomIndex := rand.Intn(len(backendServers))
			selectedBackend = backendServers[randomIndex]

			sessionMutex.Lock()
			sessions[clientID] = selectedBackend
			sessionMutex.Unlock()
		}

		targetURL := selectedBackend + ":" + port + r.URL.RequestURI()

		time := time.Now().Format("2006-01-02 15:04:05")
		fmt.Printf("[%s] Redirecting for user %s to %s\n", time, clientID, targetURL)

		w.Header().Set("Server", "HereOTT Load Balancer/1.0")

		if strings.Contains(r.UserAgent(), "okhttp") {
			http.Redirect(w, r, targetURL, http.StatusOK)
		} else {
			addCorsHeaders(w)
			http.Redirect(w, r, targetURL, http.StatusTemporaryRedirect)
		}
	})

	fmt.Printf("Starting server on port %s...\n", port)
	server := &http.Server{
		Addr:      ":" + port,
		TLSConfig: tlsConfig,
	}
	err := server.ListenAndServeTLS("assets/cert.pem", "assets/key.pem")
	if err != nil {
		fmt.Println(err)
	}
}

func getClientID(r *http.Request) string {
	ip, _, err := net.SplitHostPort(r.RemoteAddr)
	if err != nil {
		return ""
	}

	return ip
}
