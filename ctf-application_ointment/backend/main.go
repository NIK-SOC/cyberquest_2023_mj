package main

import (
	"bytes"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"math/rand"
	"net/http"
	"os"
	"strings"
	"time"

	"github.com/dgrijalva/jwt-go"
)

var secretKey = []byte("6d8441095772cfcf12a846450d8d03aadd535119d52fdaf59549887ec500ef73") // output of node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
var flag = "cq23{g0tta_c4tch_th3m_4ll_6d8441095772cfcf12a846450d}"
var webhookURL = "https://discord.com/api/webhooks/REDACTED" // replace with your own Discord webhook URL

type User struct {
	Username string
}

var validUsernames []string

func init() {
	file, err := os.Open("usernames.txt")
	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}
	defer file.Close()

	var username string
	for {
		_, err := fmt.Fscanf(file, "%s\n", &username)
		if err != nil {
			break
		}
		validUsernames = append(validUsernames, username)
	}
	fmt.Printf("Loaded %d valid usernames\n", len(validUsernames))
}

type Claims struct {
	Username string `json:"username"`
	IsAdm    int    `json:"isAdm"`
	jwt.StandardClaims
}

type Header struct {
	Alg string `json:"alg"`
	Typ string `json:"typ"`
}

type DiscordWebhookPayload struct {
	Content string `json:"content"`
	Embeds  []struct {
		Title  string `json:"title"`
		Fields []struct {
			Name   string `json:"name"`
			Value  string `json:"value"`
			Inline bool   `json:"inline"`
		} `json:"fields"`
		Color int `json:"color"`
	} `json:"embeds"`
}

func main() {
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		fmt.Printf("%s %s %s\n", r.RemoteAddr, r.Method, r.URL)
		http.NotFound(w, r)
	})

	http.HandleFunc("/login", loginHandler)
	http.HandleFunc("/appointments", apppointmentsHandler)
	http.HandleFunc("/appointment", appointmentPickHandler)

	var port = os.Getenv("BACKEND_PORT")
	if port == "" {
		port = "8080"
	}
	fmt.Printf("Listening on port %s\n", port)
	http.ListenAndServe(":"+port, nil)
}

func addCorsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "GET,OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type,Authorization")
}

func sendDiscordWebhook(ipAddress, requestHeaders string) {
	payload := DiscordWebhookPayload{
		Content: "Someone got the flag on ApplicationOintment!",
		Embeds: []struct {
			Title  string `json:"title"`
			Fields []struct {
				Name   string `json:"name"`
				Value  string `json:"value"`
				Inline bool   `json:"inline"`
			} `json:"fields"`
			Color int `json:"color"`
		}{
			{
				Title: "Client Information",
				Fields: []struct {
					Name   string `json:"name"`
					Value  string `json:"value"`
					Inline bool   `json:"inline"`
				}{
					{
						Name:   "Client IP Address",
						Value:  ipAddress,
						Inline: false,
					},
					{
						Name:   "Client Request Headers",
						Value:  requestHeaders,
						Inline: false,
					},
				},
				Color: 65280,
			},
		},
	}

	payloadJSON, err := json.Marshal(payload)
	if err != nil {
		fmt.Println("Failed to marshal JSON:", err)
		return
	}

	resp, err := http.Post(webhookURL, "application/json", bytes.NewBuffer(payloadJSON))
	if err != nil {
		fmt.Println("Failed to send Discord webhook:", err)
		return
	}
	defer resp.Body.Close()
}

func appointmentPickHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	fmt.Printf("%s %s %s\n", r.RemoteAddr, r.Method, r.URL)
	if r.Method == http.MethodOptions {
		return
	}
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var tokenString = extractTokenFromHeader(r)
	if tokenString == "" {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintf(w, `{"error": "Missing authorization header"}`)
		return
	}

	isValid, _, err := parseJWT(tokenString)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		fmt.Fprintf(w, `{"error": "%s"}`, err.Error())
		return
	}
	if !isValid {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintf(w, `{"error": "Invalid token"}`)
		return
	}

	time.Sleep(time.Duration(72+rand.Intn(318)) * time.Millisecond)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	fmt.Fprintf(w, `{"status": "ok"}`)
}

func loginHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	fmt.Printf("%s %s %s\n", r.RemoteAddr, r.Method, r.URL)
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
	username := r.FormValue("username")

	if !isValidUsername(username) {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintf(w, `{"error": "Invalid username"}`)
		return
	}

	tokenString, expirationTime, err := generateJWT(username)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		fmt.Fprintf(w, `{"error": "%s"}`, err.Error())
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	fmt.Fprintf(w, `{"token": "%s", "exp": %d}`, tokenString, expirationTime.Unix())
}

func isValidUsername(username string) bool {
	for _, validUsername := range validUsernames {
		if validUsername == username {
			return true
		}
	}
	return false
}

func apppointmentsHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	fmt.Printf("%s %s %s\n", r.RemoteAddr, r.Method, r.URL)
	if r.Method == http.MethodOptions {
		return
	}
	if r.Method != http.MethodGet {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var tokenString = extractTokenFromHeader(r)
	if tokenString == "" {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintf(w, `{"error": "Missing authorization header"}`)
		return
	}

	isValid, isAdmin, err := parseJWT(tokenString)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		fmt.Fprintf(w, `{"error": "%s"}`, err.Error())
		return
	}
	if !isValid {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintf(w, `{"error": "Invalid token"}`)
		return
	}
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	if isAdmin {
		go sendDiscordWebhook(r.RemoteAddr, fmt.Sprintf("```%s```", formatHeaders(r.Header)))
		fmt.Fprintf(w, `{"appointments": [{"name":"Bust the Ghostly Buffet Bash","date":1677158400},{"name":"Ecto-1 Parade Extravaganza","date":1677244800},{"name":"Slimer's All-You-Can-Slime Buffet","date":1677331200},{"name":"Proton Pack Painting Party","date":1677417600},{"name":"Ghostbusters Trivia Night","date":1677504000},{"name":"Stay Puft Marshmallow Roast","date":1677590400}, {"name":"%s","date":1677676800}]}`, flag)
		return
	}
	fmt.Fprintf(w, `{"appointments": [{"name":"Bust the Ghostly Buffet Bash","date":1677158400},{"name":"Ecto-1 Parade Extravaganza","date":1677244800},{"name":"Slimer's All-You-Can-Slime Buffet","date":1677331200},{"name":"Proton Pack Painting Party","date":1677417600},{"name":"Ghostbusters Trivia Night","date":1677504000},{"name":"Stay Puft Marshmallow Roast","date":1677590400}]}`)
}

func formatHeaders(headers http.Header) string {
	var formattedHeaders string

	for key, values := range headers {
		formattedHeaders += fmt.Sprintf("%s: %s\n", key, values[0])
	}

	if len(formattedHeaders) > 1000 {
		formattedHeaders = formattedHeaders[:1000]
	}

	return formattedHeaders
}

func parseJWT(tokenString string) (bool, bool, error) {
	var tokenParts = strings.Split(tokenString, ".")
	if len(tokenParts) != 3 {
		return false, false, nil
	}
	var firstPart, base64Error = base64.RawURLEncoding.DecodeString(tokenParts[0])
	if base64Error != nil {
		return false, false, base64Error
	}
	var header Header
	var jsonParseError = json.Unmarshal(firstPart, &header)
	if jsonParseError != nil {
		return false, false, jsonParseError
	}
	if strings.ToLower(header.Alg) == "none" {
		var secondPart, base64Error = base64.RawURLEncoding.DecodeString(tokenParts[1])
		if base64Error != nil {
			return false, false, base64Error
		}
		var claims Claims
		var jsonParseError = json.Unmarshal(secondPart, &claims)
		if jsonParseError != nil {
			return false, false, jsonParseError
		}
		if claims.IsAdm == 1 {
			return true, true, nil
		}
		return true, false, nil
	}
	token, err := jwt.Parse(tokenString, func(token *jwt.Token) (interface{}, error) {
		return secretKey, nil
	})
	if err != nil {
		return false, false, err
	}
	return token.Valid, false, nil
}

func extractTokenFromHeader(r *http.Request) string {
	authorizationHeader := r.Header.Get("Authorization")
	if authorizationHeader == "" {
		return ""
	}

	tokenParts := strings.Split(authorizationHeader, " ")
	if len(tokenParts) != 2 || strings.ToLower(tokenParts[0]) != "bearer" {
		return ""
	}

	return tokenParts[1]
}

func generateJWT(username string) (string, time.Time, error) {
	expirationTime := time.Now().Add(5 * time.Minute)

	claims := &Claims{
		Username: username,
		IsAdm:    0,
		StandardClaims: jwt.StandardClaims{
			ExpiresAt: expirationTime.Unix(),
		},
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)

	tokenString, err := token.SignedString(secretKey)
	if err != nil {
		return "", expirationTime, err
	}

	return tokenString, expirationTime, nil
}
