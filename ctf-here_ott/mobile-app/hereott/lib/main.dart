import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:package_info_plus/package_info_plus.dart';

import 'dart:convert';
import 'package:uuid/uuid.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

import 'package:flutter_okhttp/flutter_okhttp.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_jailbreak_detection/flutter_jailbreak_detection.dart';
import 'qr_window.dart';
import 'static.dart' show domain;

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
            seedColor: Colors.deepPurple, brightness: Brightness.dark),
      ),
      home: const MyHomePage(),
    );
  }
}

class MyHomePage extends StatefulWidget {
  const MyHomePage({super.key});

  @override
  _MyHomePageState createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  String? appName;
  String? apiVersion;
  bool isTyping = false;
  Uuid? deviceUuid;
  String? baseUrl;
  FocusNode usernameFocusNode = FocusNode();
  FocusNode passwordFocusNode = FocusNode();
  TextEditingController usernameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  http.Client httpClient = FlutterOkhttp().createDartHttpClient();

  void showAlertWithMessage(BuildContext context, String message) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return WillPopScope(
          onWillPop: () async => false,
          child: AlertDialog(
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const FaIcon(
                      FontAwesomeIcons.xmark,
                      color: Colors.red,
                      size: 100,
                    ),
                    const Divider(
                      color: Colors.grey,
                      thickness: 1,
                    ),
                    Flexible(
                      child: Text(
                        message,
                        textAlign: TextAlign.center,
                      ),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.of(context).pop();
                        exit(0);
                      },
                      child: const Text("Close App"),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void fetchBaseUrl() async {
    http.Response response;

    try {
      response = await httpClient.head(Uri.https(domain, '/'));
    } on PlatformException catch (e) {
      switch (e.code) {
        case 'ON_HTTP_DNS_RESOLUTION_FAILURE':
          Future.microtask(() {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('DNS resolution failed!'),
              ),
            );
          });
          break;
        case 'ON_HTTP_ERROR':
          if (e.message!.contains('CertPathValidatorException')) {
            Future.microtask(() {
              showAlertWithMessage(context,
                  'SSL pinning has been tampered with!\n\nPlease run this app on an unmodified device!');
            });
          } else {
            Future.microtask(() {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('HTTP error: $e!'),
                ),
              );
            });
          }
          break;
        default:
          Future.microtask(() {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Unknown error!'),
              ),
            );
          });
          break;
      }
      return;
    }

    if (response.statusCode == 200) {
      setState(() {
        baseUrl = response.headers['location']!.split('/')[2];
        if (baseUrl != null) {
          String ip = baseUrl!.split(':')[0];
          FlutterOkhttp().addTrustedHost(ip);
        }
      });
    } else {
      Future.microtask(() {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to fetch base URL!'),
          ),
        );
      });
    }
    if (baseUrl != null) fetchData();
  }

  void fetchData() async {
    deviceUuid ??= const Uuid();
    final params = {
      'username': 'HereOttMobileApp',
      'password': 'OTc5NjdhZjBkYjQ3OGU4NDJlMTZkYmY3YWVhNmU5M2E',
      'version': '1.0.0',
      'app': 'hu.honeylab.cyberquest.hereott',
      'uuid': deviceUuid!.v4(),
    };
    final uri = Uri.https(baseUrl!, '/v1/config', params);

    http.Response response;

    try {
      response = await httpClient.get(uri, headers: {
        'X-Platform': 'Android',
      });
    } on PlatformException catch (e) {
      switch (e.code) {
        case 'ON_HTTP_DNS_RESOLUTION_FAILURE':
          Future.microtask(() {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('DNS resolution failed!'),
              ),
            );
          });
          break;
        case 'ON_HTTP_ERROR':
          if (e.message!.contains('CertPathValidatorException')) {
            Future.microtask(() {
              showAlertWithMessage(context,
                  'SSL pinning has been tampered with!\n\nPlease run this app on an unmodified device!');
            });
          } else {
            Future.microtask(() {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('HTTP error: $e!'),
                ),
              );
            });
          }
          break;
        default:
          Future.microtask(() {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Unknown error!'),
              ),
            );
          });
          break;
      }
      return;
    }

    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      setState(() {
        appName = jsonData['Version']['appName'];
        apiVersion = jsonData['apiVersion'];
      });
    } else {
      Future.microtask(() {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to fetch app name!'),
          ),
        );
      });
    }
  }

  void submitData() async {
    final username = usernameController.text;
    final password = passwordController.text;

    final response = await httpClient.post(
      Uri.https(baseUrl!, '/v1/login'),
      headers: {
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'username': username,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      Future.microtask(() {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Login success!'),
          ),
        );
      });
    } else if (response.statusCode == 401) {
      final error = json.decode(response.body)['error'];
      Future.microtask(() {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Invalid credentials ($error)!'),
          ),
        );
      });
    } else {
      Future.microtask(() {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to login!'),
          ),
        );
      });
    }
  }

  Future<bool> checkIntegrity() async {
    if (kReleaseMode) {
      PackageInfo packageInfo = await PackageInfo.fromPlatform();
      String buildSignature = packageInfo.buildSignature;

      if (buildSignature != "F64E4626A7E33C2DAD9669249337B69A8E124B33") {
        Future.microtask(() {
          showAlertWithMessage(context,
              'App has been tampered with!\n\nPlease make sure that you are running the official app!');
        });
        return false;
      }
    }
    return true;
  }

  void checkRootAndStartFetch() async {
    final isRooted = await FlutterJailbreakDetection.jailbroken;
    final isIntegrityOk = await checkIntegrity();
    if (isRooted) {
      Future.microtask(() {
        showAlertWithMessage(context,
            'Root detected!\n\nPlease run this app on an unmodified device!');
      });
    } else if (isIntegrityOk) {
      fetchBaseUrl();
    }
  }

  @override
  void initState() {
    super.initState();
    usernameFocusNode.addListener(_handleFocusChange);
    passwordFocusNode.addListener(_handleFocusChange);
    httpClient = FlutterOkhttp().createDartHttpClient();
    FlutterOkhttp().addTrustedCaCert('cert.pem');
    FlutterOkhttp().addTrustedHost(domain);
    checkRootAndStartFetch();
  }

  void _handleFocusChange() {
    if (usernameFocusNode.hasFocus || passwordFocusNode.hasFocus) {
      setState(() {
        isTyping = true;
      });
    } else {
      setState(() {
        isTyping = false;
      });
    }
  }

  @override
  void dispose() {
    usernameFocusNode.dispose();
    passwordFocusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Stack(
          alignment: Alignment.center,
          children: <Widget>[
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                const FaIcon(FontAwesomeIcons.userAstronaut, size: 80),
                const SizedBox(height: 16),
                Text(
                  appName == null ? 'Loading...' : '$appName Login',
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                const SizedBox(
                  width: 300,
                  child: Divider(
                    color: Colors.grey,
                    thickness: 1,
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  focusNode: usernameFocusNode,
                  controller: usernameController,
                  decoration: InputDecoration(
                    labelText: 'Username',
                    enabled: appName != null,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  focusNode: passwordFocusNode,
                  controller: passwordController,
                  obscureText: true,
                  enabled: appName != null,
                  decoration: const InputDecoration(
                    labelText: 'Password',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed:
                      appName == null ? checkRootAndStartFetch : submitData,
                  child: Text(appName == null ? 'Retry' : 'Login'),
                ),
                Visibility(
                  visible: appName != null,
                  child: ElevatedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (context) => ScannerScreen(
                                  httpClient: httpClient,
                                  deviceUuid: deviceUuid!,
                                  baseUrl: baseUrl!)),
                        );
                      },
                      child: const Text('Login with QR Code')),
                ),
              ],
            ),
            AnimatedOpacity(
              opacity: isTyping ? 0.0 : 1.0,
              duration: const Duration(milliseconds: 500),
              child: Align(
                alignment: Alignment.bottomCenter,
                child: Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: Text(
                    'Copyright HereOTT\nApp version: 1.0.0+1\n${apiVersion == null ? 'API version: N/A' : 'API version: $apiVersion'}',
                    style: const TextStyle(
                      fontSize: 10,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
