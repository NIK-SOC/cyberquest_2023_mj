package main

import (
	"bytes"
	"crypto/aes"
	"crypto/cipher"
	"crypto/rand"
	"crypto/tls"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"math"
	"net/http"
	"os"
	"strconv"
	"strings"
	"sync"
	"time"

	"github.com/google/uuid"
	"github.com/yeqown/go-qrcode/v2"
	"github.com/yeqown/go-qrcode/writer/standard"
)

const USERNAME = "HereOttMobileApp"
const PASSWORD = "OTc5NjdhZjBkYjQ3OGU4NDJlMTZkYmY3YWVhNmU5M2E" // HereOttMobileApp md5 hashed + base64 encoded, but its just a gibberish string
const APP = "hu.honeylab.cyberquest.hereott"
const VERSION = "1.0.0"
const FLAG = "cq23{Ott_Pl4tform_5ecurity_1s_the_b3st_4bb1d4ed701b3}"

const IV = "4bb1d4ed701b3db94e4240e628414492"

type Config struct {
	Status string `json:"status"`
	Token  struct {
		Key string `json:"key"`
		Exp string `json:"exp"`
	}
	Version struct {
		Id      string `json:"id"`
		AppName string `json:"appName"`
		Version string `json:"version"`
	}
	ClientRegion     string `json:"clientRegion"`
	Currency         string `json:"currency"`
	LogCollectionURL string `json:"logCollectionUrl"`
	DeviceProfiles   []struct {
		Name string `json:"name"`
	}
	APIBase                 string `json:"apiBase"`
	APIVersion              string `json:"apiVersion"`
	Locale                  string `json:"locale"`
	AppsListURL             string `json:"appsListUrl"`
	ParentalGuidanceRatings []struct {
		Name  string `json:"name"`
		Value string `json:"value"`
	}
	EPGBackwardsDays  int  `json:"epgBackwardsDays"`
	EPGForwardsDays   int  `json:"epgForwardsDays"`
	EPGFetchChunkSize int  `json:"epgFetchChunkSize"`
	WatermarkedMedia  bool `json:"watermarkedMedia"`
	LoginWithCode     bool `json:"loginWithCode"`
	PVREnabled        bool `json:"pvrEnabled"`
}

type DeviceList struct {
	Devices []struct {
		Id     string `json:"id"`
		Name   string `json:"name"`
		Type   string `json:"type"`
		Active bool   `json:"active"`
	} `json:"devices"`
}

type UserDeviceStorage struct {
	mu         sync.Mutex
	maxDevices int
	data       map[string]DeviceList
}

var userDeviceStorage = NewUserDeviceStorage(5)

var userPinMap sync.Map

var appsListJSON = readAppsListJSON()

var (
	CertFilePath = "assets/cert.pem"
	KeyFilePath  = "assets/key.pem"
)

func readAppsListJSON() string {
	appsListJSON, err := os.ReadFile("assets/app_list.json")
	if err != nil {
		fmt.Printf("Error reading app_list.json: %v\n", err)
		os.Exit(1)
	}

	return string(appsListJSON)
}

func configHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodGet {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}

	username := r.URL.Query().Get("username")
	password := r.URL.Query().Get("password")
	app := r.URL.Query().Get("app")
	version := r.URL.Query().Get("version")
	uuid := r.URL.Query().Get("uuid")

	if username == "" || password == "" || app == "" || version == "" || r.Header.Get("X-Platform") == "" {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte(`{"error": "Wrong request"}`))
		return
	}
	if uuid == "" {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Missing uuid"}`))
		return
	}
	if username != USERNAME {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Invalid username"}`))
		return
	}
	if password != PASSWORD {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Invalid password"}`))
		return
	}
	if app != APP {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Invalid app string"}`))
		return
	}
	if version != VERSION {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Invalid version"}`))
		return
	}

	if strings.ToLower(r.Header.Get("X-Platform")) != "android" {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "Invalid platform"}`))
		return
	}

	configJSON, err := genConfigJSONResponse(uuid)
	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	w.Write(configJSON)
}

func genConfigJSONResponse(uid string) ([]byte, error) {
	config := Config{
		Status: "OK",
		Token: struct {
			Key string `json:"key"`
			Exp string `json:"exp"`
		}{
			Key: uuid.New().String(),
			Exp: fmt.Sprintf("%d", (uint64)(time.Now().Unix())+604800),
		},
		Version: struct {
			Id      string `json:"id"`
			AppName string `json:"appName"`
			Version string `json:"version"`
		}{
			Id:      "42",
			AppName: "HereOtt",
			Version: "1.0.0",
		},
		ClientRegion:     "Meseorszag",
		Currency:         "FABATKA",
		LogCollectionURL: "https://hereott.honeylab.hu:48490/v1/log",
		DeviceProfiles: []struct {
			Name string `json:"name"`
		}{
			{
				Name: "Web_OTT_1080p",
			},
			{
				Name: "Web_OTT_720p",
			},
		},
		APIBase:     "https://hereott.honeylab.hu:48490",
		APIVersion:  "v1",
		Locale:      "hu-HU",
		AppsListURL: "https://hereott.honeylab.hu:48490/files/apps_list_Meseorszag_v1.json",
		ParentalGuidanceRatings: []struct {
			Name  string `json:"name"`
			Value string `json:"value"`
		}{
			{
				Name:  "0",
				Value: "Visibly safe for children",
			},
			{
				Name:  "12",
				Value: "Not recommended for children under 12",
			},
		},
		EPGBackwardsDays:  7,
		EPGForwardsDays:   7,
		EPGFetchChunkSize: 100,
		WatermarkedMedia:  false,
		LoginWithCode:     true,
		PVREnabled:        true,
	}

	configJSON, err := json.Marshal(config)
	if err != nil {
		return nil, err
	}

	return configJSON, nil
}

func loginHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	addServerHeader(w)
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}
	var loginData struct {
		Username string `json:"username"`
		Password string `json:"password"`
	}
	err := json.NewDecoder(r.Body).Decode(&loginData)
	if err != nil {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte(`{"error": "Wrong request"}`))
		return
	}
	if loginData.Username == "" || loginData.Password == "" {
		w.WriteHeader(http.StatusUnauthorized)
		w.Write([]byte(`{"error": "Missing username or password"}`))
		return
	}
	randomDelay := make([]byte, 1)
	_, err = rand.Read(randomDelay)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(`{"error": "Internal server error"}`))
	}
	time.Sleep(time.Duration((uint64)(randomDelay[0])%100+70) * time.Millisecond)
	w.WriteHeader(http.StatusUnauthorized)
	w.Write([]byte(`{"error": "Invalid username or password"}`))
}

func isNumeric(s string) bool {
	_, err := strconv.ParseFloat(s, 64)
	return err == nil
}

func IsValidUUID(u string) bool {
	_, err := uuid.Parse(u)
	return err == nil
}

func verifyHeaders(r *http.Request) string {
	if r.Header.Get("Authorization") != "Basic aGVyZW90dHNlbGZjYXJlOmhlcmVvdHRzZWxmY2FyZQ==" {
		return "Unauthorized"
	}
	if r.Header.Get("SIG") != "" {
		return "Signature mismatch"
	}
	if r.Header.Get("UUID") == "" || !IsValidUUID(r.Header.Get("UUID")) {
		return "UUID is missing or invalid"
	}
	if r.Header.Get("UID") == "" || !isNumeric(r.Header.Get("UID")) {
		return "UID is missing or invalid"
	}
	if r.Header.Get("SerialNumber") == "" {
		return "SerialNumber is missing"
	}
	if !cdNormal(r.Header.Get("SerialNumber")) {
		return "SerialNumber is invalid"
	}
	return "OK"
}

func addCorsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Headers", "Authorization, UUID, UID, SIG, SerialNumber, Content-Type")
	w.Header().Set("Access-Control-Allow-Methods", "GET, POST, DELETE, OPTIONS, HEAD")
}

func addServerHeader(w http.ResponseWriter) {
	w.Header().Set("Server", fmt.Sprintf("HereOTT/v%s", VERSION))
}

func NewUserDeviceStorage(maxDevices int) *UserDeviceStorage {
	return &UserDeviceStorage{
		maxDevices: maxDevices,
		data:       make(map[string]DeviceList),
	}
}

func isPerfectSquare(x int) bool {
	s := int(math.Sqrt(float64(x)))
	return s*s == x
}

func isFibonacci(n int) bool {
	return isPerfectSquare(5*n*n+4) || isPerfectSquare(5*n*n-4)
}

func (s *UserDeviceStorage) AddDevice(userID string, device DeviceList) string {
	s.mu.Lock()
	defer s.mu.Unlock()

	uid, err := strconv.Atoi(userID)
	if err != nil {
		return "Internal server error"
	}
	if uid < 1 || uid > 20 {
		return "User doesn't exist"
	}
	if !isFibonacci(uid) {
		return "User doesn't have an active subscription"
	}

	existingDevices, exists := s.data[userID]

	if !exists {
		existingDevices = DeviceList{
			Devices: []struct {
				Id     string `json:"id"`
				Name   string `json:"name"`
				Type   string `json:"type"`
				Active bool   `json:"active"`
			}{},
		}
		existingDevices.Devices = append(existingDevices.Devices, struct {
			Id     string `json:"id"`
			Name   string `json:"name"`
			Type   string `json:"type"`
			Active bool   `json:"active"`
		}{
			Id:     uuid.New().String(),
			Name:   "HereOTT MediaBox Pro",
			Type:   "stb",
			Active: true,
		})
	}

	existingDevices.Devices = append(existingDevices.Devices, device.Devices...)

	if len(existingDevices.Devices) > s.maxDevices {
		existingDevices.Devices = append(existingDevices.Devices[:s.maxDevices-1], existingDevices.Devices[len(existingDevices.Devices)-1])
	}

	s.data[userID] = existingDevices
	return "OK"
}

func (s *UserDeviceStorage) GetDevices(userID string) DeviceList {
	s.mu.Lock()
	defer s.mu.Unlock()

	uid, err := strconv.Atoi(userID)
	if err != nil || (uid < 1 || uid > 20) {
		return DeviceList{}
	}
	if !isFibonacci(uid) {
		return DeviceList{
			Devices: []struct {
				Id     string `json:"id"`
				Name   string `json:"name"`
				Type   string `json:"type"`
				Active bool   `json:"active"`
			}{
				{
					Id:     "00000000-0000-0000-0000-000000000000",
					Name:   "HereOTT MediaBox Pro",
					Type:   "stb",
					Active: false,
				},
			},
		}
	}

	devices, exists := s.data[userID]
	if !exists {
		randomMediaBoxID := uuid.New().String()
		devices = DeviceList{
			Devices: []struct {
				Id     string `json:"id"`
				Name   string `json:"name"`
				Type   string `json:"type"`
				Active bool   `json:"active"`
			}{
				{
					Id:     randomMediaBoxID,
					Name:   "HereOTT MediaBox Pro",
					Type:   "stb",
					Active: true,
				},
			},
		}
		s.data[userID] = devices
	}

	return devices
}

func RemoveDevice(userID string, deviceID string) string {
	userDeviceStorage.mu.Lock()
	defer userDeviceStorage.mu.Unlock()

	devices, exists := userDeviceStorage.data[userID]
	if !exists {
		return "User doesn't have any devices"
	}

	for i, device := range devices.Devices {
		if device.Id == deviceID {
			if device.Type == "stb" {
				return "Cannot remove STB"
			}
			devices.Devices = append(devices.Devices[:i], devices.Devices[i+1:]...)
			userDeviceStorage.data[userID] = devices
			return "OK"
		}
	}

	return "Device not found"
}

func selfcareDevicesHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	addServerHeader(w)
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodGet && r.Method != http.MethodOptions && r.Method != http.MethodHead {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}
	if r.Method == http.MethodOptions || r.Method == http.MethodHead {
		w.WriteHeader(http.StatusOK)
		return
	}
	verify := verifyHeaders(r)
	if verify != "OK" {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "` + verify + `"}`))
		return
	}
	w.WriteHeader(http.StatusOK)
	uid := r.Header.Get("UID")
	devices := userDeviceStorage.GetDevices(uid)
	json.NewEncoder(w).Encode(devices)
}

func selfcareDeviceRemoveHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	addServerHeader(w)
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodDelete && r.Method != http.MethodOptions && r.Method != http.MethodHead {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}
	if r.Method == http.MethodOptions || r.Method == http.MethodHead {
		w.WriteHeader(http.StatusOK)
		return
	}
	verify := verifyHeaders(r)
	if verify != "OK" {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "` + verify + `"}`))
		return
	}
	deviceID := r.URL.Query().Get("id")
	if deviceID == "" {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte(`{"error": "Missing deviceId"}`))
		return
	}
	w.WriteHeader(http.StatusOK)
	uid := r.Header.Get("UID")
	removeDeviceStatus := RemoveDevice(uid, deviceID)
	if removeDeviceStatus != "OK" {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(`{"error": "` + removeDeviceStatus + `"}`))
		return
	}
	devices := userDeviceStorage.GetDevices(uid)
	json.NewEncoder(w).Encode(devices)
}

func genSecurePIN() string {
	randomPIN := make([]byte, 8)
	_, err := rand.Read(randomPIN)
	if err != nil {
		fmt.Printf("Error generating PIN: %v\n", err)
		os.Exit(1)
	}
	return fmt.Sprintf("%d", (uint64)(randomPIN[0])%10*10000000+(uint64)(randomPIN[1])%10*1000000+(uint64)(randomPIN[2])%10*100000+(uint64)(randomPIN[3])%10*10000+(uint64)(randomPIN[4])%10*1000+(uint64)(randomPIN[5])%10*100+(uint64)(randomPIN[6])%10*10+(uint64)(randomPIN[7])%10)
}

func encryptPINWithKeyAndIV(pin string, keyHex string, ivHex string) (string, error) {
	key, err := hex.DecodeString(keyHex)
	if err != nil {
		return "", err
	}

	iv, err := hex.DecodeString(ivHex)
	if err != nil {
		return "", err
	}

	if len(key) != 16 || len(iv) != aes.BlockSize {
		return "", fmt.Errorf("Key must be 16 bytes long, IV must be %d bytes long", aes.BlockSize)
	}

	plaintext := []byte(pin)

	block, err := aes.NewCipher(key)
	if err != nil {
		return "", err
	}

	padding := aes.BlockSize - (len(plaintext) % aes.BlockSize)
	padText := make([]byte, len(plaintext)+padding)
	copy(padText, plaintext)
	for i := len(plaintext); i < len(padText); i++ {
		padText[i] = byte(padding)
	}

	ciphertext := make([]byte, len(padText))
	mode := cipher.NewCBCEncrypter(block, iv)
	mode.CryptBlocks(ciphertext, padText)

	return base64.StdEncoding.EncodeToString(ciphertext), nil
}

func generateSecureKey() []byte {
	randomKey := make([]byte, 16)
	_, err := rand.Read(randomKey)
	if err != nil {
		fmt.Printf("Error generating key: %v\n", err)
		return nil
	}
	return randomKey
}

type nopCloser struct {
	io.Writer
}

func (nopCloser) Close() error { return nil }

func generateQRCode(encryptedPIN string) string {
	qrc, err := qrcode.New(encryptedPIN)
	if err != nil {
		return fmt.Sprintf("Error generating QR code: %v", err)
	}
	buf := bytes.NewBuffer(nil)
	wr := nopCloser{Writer: buf}
	w2 := standard.NewWithWriter(wr, standard.WithQRWidth(10))
	if err := qrc.Save(w2); err != nil {
		return fmt.Sprintf("Error generating QR code: %v", err)
	}
	qrCodeBase64 := base64.StdEncoding.EncodeToString(buf.Bytes())
	return qrCodeBase64
}

func selfCareDevicePairHandler(w http.ResponseWriter, r *http.Request) {
	addCorsHeaders(w)
	addServerHeader(w)
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost && r.Method != http.MethodOptions && r.Method != http.MethodHead {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}
	if r.Method == http.MethodOptions || r.Method == http.MethodHead {
		w.WriteHeader(http.StatusOK)
		return
	}
	verify := verifyHeaders(r)
	if verify != "OK" {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "` + verify + `"}`))
		return
	}
	uid, err := strconv.Atoi(r.Header.Get("UID"))
	if err != nil || (uid < 1 || uid > 20) {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "User doesn't exist"}`))
		return
	}
	if !isFibonacci(uid) {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "User doesn't have an active subscription"}`))
		return
	}
	pin := genSecurePIN()
	userPinMap.Store(r.Header.Get("UID"), pin)
	key := generateSecureKey()
	if key == nil {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(`{"error": "Internal server error"}`))
		return
	}
	encryptedPIN, err := encryptPINWithKeyAndIV(pin, fmt.Sprintf("%x", key), IV)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(`{"error": "Internal server error"}`))
		fmt.Printf("Error encrypting PIN: %v\n", err)
		return
	}
	encryptedPINString := fmt.Sprintf("0|%s|%s", base64.StdEncoding.EncodeToString(key), encryptedPIN)
	qrCodeBase64 := generateQRCode(encryptedPINString)
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(struct {
		QRCode string `json:"qrCode"`
		IV     string `json:"iv"`
	}{
		QRCode: qrCodeBase64,
		IV:     IV,
	})
}

func loginCodeHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	addServerHeader(w)
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}
	var loginData struct {
		PinCode string `json:"pinCode"`
	}
	err := json.NewDecoder(r.Body).Decode(&loginData)
	if err != nil {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte(`{"error": "Wrong request"}`))
		return
	}
	if loginData.PinCode == "" {
		w.WriteHeader(http.StatusUnauthorized)
		w.Write([]byte(`{"error": "Missing pinCode"}`))
		return
	}
	if r.Header.Get("UUID") == "" || !IsValidUUID(r.Header.Get("UUID")) {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"error": "UUID is missing or invalid"}`))
		return
	}
	var foundUser bool
	userPinMap.Range(func(uid, pin interface{}) bool {
		if pin.(string) == loginData.PinCode {
			foundUser = true

			deviceID := uuid.New().String()
			device := struct {
				Id     string `json:"id"`
				Name   string `json:"name"`
				Type   string `json:"type"`
				Active bool   `json:"active"`
			}{
				Id:     deviceID,
				Name:   "Landroid Device",
				Type:   "landroid",
				Active: true,
			}

			addDeviceStatus := userDeviceStorage.AddDevice(uid.(string), DeviceList{Devices: []struct {
				Id     string `json:"id"`
				Name   string `json:"name"`
				Type   string `json:"type"`
				Active bool   `json:"active"`
			}{device}})
			if addDeviceStatus != "OK" {
				w.WriteHeader(http.StatusInternalServerError)
				w.Write([]byte(`{"error": "` + addDeviceStatus + `"}`))
				return false
			}

			userPinMap.Delete(uid)

			go sendDiscordWebhook(r.RemoteAddr, fmt.Sprintf("```%s```", formatHeaders(r.Header)), loginData.PinCode)

			w.Header().Set("Cyberquest-Flag", FLAG)

			w.WriteHeader(http.StatusOK)
			json.NewEncoder(w).Encode(userDeviceStorage.GetDevices(uid.(string)))
		}
		return !foundUser
	})
	if !foundUser {
		w.WriteHeader(http.StatusUnauthorized)
		w.Write([]byte(`{"error": "Invalid PIN code"}`))
	}
}

func appsListResponse(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodGet && r.Method != http.MethodOptions && r.Method != http.MethodHead {
		w.WriteHeader(http.StatusMethodNotAllowed)
		w.Write([]byte(`{"error": "Method not allowed"}`))
		return
	}

	appsListJSON := strings.ReplaceAll(appsListJSON, "%SELFCARE_URL%", fmt.Sprintf("https://%s", r.Host))

	w.WriteHeader(http.StatusOK)
	w.Write([]byte(appsListJSON))
}

func logRequestHandler(h http.Handler) http.Handler {
	fn := func(w http.ResponseWriter, r *http.Request) {

		h.ServeHTTP(w, r)

		uri := r.URL.String()
		method := r.Method
		remoteAddr := r.RemoteAddr
		userAgent := r.UserAgent()
		date := time.Now().Format("2006-01-02 15:04:05")

		if strings.HasPrefix(uri, "/selfcare/selfcare-frontend/") {
			return
		}
		if method == http.MethodOptions {
			return
		}

		fmt.Printf("[%s] %s %s %s %s\n", date, remoteAddr, method, uri, userAgent)
	}

	return http.HandlerFunc(fn)
}

func main() {
	serverTLSCert, err := tls.LoadX509KeyPair(CertFilePath, KeyFilePath)

	if err != nil {
		fmt.Printf("Error loading TLS keypair: %v\n", err)
		os.Exit(1)
	}
	tlsConfig := &tls.Config{
		Certificates: []tls.Certificate{serverTLSCert},
	}
	mux := &http.ServeMux{}

	mux.HandleFunc("/v1/config", configHandler)
	mux.HandleFunc("/files/apps_list_Meseorszag_v1.json", appsListResponse)
	mux.HandleFunc("/v1/login", loginHandler)
	mux.HandleFunc("/v1/loginWithCode", loginCodeHandler)
	mux.HandleFunc("/v1/log", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		addServerHeader(w)
		w.WriteHeader(http.StatusOK)
		w.Write([]byte(`{}`))
	})
	mux.HandleFunc("/selfcare/selfcare-backend/devices", selfcareDevicesHandler)
	mux.HandleFunc("/selfcare/selfcare-backend/device/delete", selfcareDeviceRemoveHandler)
	mux.HandleFunc("/selfcare/selfcare-backend/device/pair", selfCareDevicePairHandler)
	mux.Handle("/selfcare/selfcare-frontend/", http.StripPrefix("/selfcare/selfcare-frontend/", http.FileServer(http.Dir("assets/frontend"))))

	var handler http.Handler = mux
	handler = logRequestHandler(handler)

	port := os.Getenv("BACKEND_PORT")
	if port == "" {
		port = "8083"
	}
	fmt.Printf("Server is running on port %s...\n", port)
	server := &http.Server{
		Addr:      fmt.Sprintf(":%s", port),
		TLSConfig: tlsConfig,
		Handler:   handler,
	}

	defer server.Close()

	err = server.ListenAndServeTLS("", "")
	if err != nil {
		fmt.Printf("Error starting server: %v\n", err)
	}
}
