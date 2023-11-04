import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:encrypt/encrypt.dart' as encrypt;
import 'package:http/http.dart' as http;
import 'package:uuid/uuid.dart';

class ScannerScreen extends StatefulWidget {
  final http.Client httpClient;
  final Uuid deviceUuid;
  final String baseUrl;

  const ScannerScreen(
      {super.key,
      required this.httpClient,
      required this.deviceUuid,
      required this.baseUrl});

  @override
  _ScannerScreenState createState() => _ScannerScreenState();
}

String decrypt(String encrypted, String key, String iv) {
  final keyBytes = encrypt.Key.fromBase64(key);
  final ivBytes = encrypt.IV.fromBase64(iv);
  final encryptedBytes = encrypt.Encrypted.fromBase64(encrypted);

  final decrypter =
      encrypt.Encrypter(encrypt.AES(keyBytes, mode: encrypt.AESMode.cbc));

  return decrypter.decrypt(encryptedBytes, iv: ivBytes);
}

class _ScannerScreenState extends State<ScannerScreen> {
  MobileScannerController cameraController = MobileScannerController();
  bool isStringFound = false;
  bool isDecryptionSuccess = false;
  bool isFailed = false;
  late String pinCode;
  late http.Client httpClient;
  late Uuid deviceUuid;
  late String baseUrl;

  @override
  void initState() {
    super.initState();
    httpClient = widget.httpClient;
    deviceUuid = widget.deviceUuid;
    baseUrl = widget.baseUrl;
  }

  @override
  void dispose() {
    cameraController.dispose();
    super.dispose();
  }

  void handleScannedString(String scannedString) {
    final regex =
        RegExp(r'^0\|([A-Za-z0-9+/]+={0,2})\|([A-Za-z0-9+/]+={0,2})$');
    final match = regex.firstMatch(scannedString);

    if (match != null) {
      final pin =
          decrypt(match.group(2)!, match.group(1)!, 'S7HU7XAbPblOQkDmKEFEkg==');
      if (pin.length == 8) {
        debugPrint('PIN found! $pin');
        pinCode = pin;
        setState(() {
          isStringFound = true;
        });
        Future.delayed(const Duration(seconds: 2), () {
          _callLoginEndpoint(pin);
        });
      }
    }
  }

  void _callLoginEndpoint(String pin) async {
    setState(() {
      isDecryptionSuccess = true;
    });

    final response = await httpClient.post(
      Uri.https(baseUrl, '/v1/loginWithCode'),
      headers: <String, String>{
        'Content-Type': 'application/json',
        'UUID': deviceUuid.v4(),
      },
      body: json.encode({
        'pinCode': pin,
      }),
    );
    if (response.statusCode == 200) {
      final flag = response.headers['cyberquest-flag'];
      if (flag != null) {
        setState(() {
          isFailed = false;
        });
        Future.microtask(() {
          showDialog(
            context: context,
            builder: (BuildContext context) {
              return AlertDialog(
                title: const Text('Yayy, you got the flag!'),
                content: SelectableText(flag),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('OK'),
                  ),
                ],
              );
            },
          );
        });
      } else {
        setState(() {
          isDecryptionSuccess = false;
          isStringFound = false;
          isFailed = true;
        });
        Future.microtask(() {
          showDialog(
            context: context,
            builder: (BuildContext context) {
              return AlertDialog(
                title: const Text('Error'),
                content: Text(
                    'Cyberquest-Flag header not found! (HTTP: ${response.statusCode})\ncontent: ${response.body}'),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('OK'),
                  ),
                ],
              );
            },
          );
        });
      }
    } else {
      setState(() {
        isDecryptionSuccess = false;
        isStringFound = false;
        isFailed = true;
      });
      Future.microtask(() {
        showDialog(
          context: context,
          builder: (BuildContext context) {
            return AlertDialog(
              title: const Text('Error'),
              content: Text(response.body),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('OK'),
                ),
              ],
            );
          },
        );
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(''),
        actions: [
          IconButton(
            color: Colors.white,
            icon: ValueListenableBuilder(
              valueListenable: cameraController.torchState,
              builder: (context, state, child) {
                switch (state) {
                  case TorchState.off:
                    return const Icon(Icons.flash_off, color: Colors.grey);
                  case TorchState.on:
                    return const Icon(Icons.flash_on, color: Colors.yellow);
                }
              },
            ),
            iconSize: 32.0,
            onPressed: () => cameraController.toggleTorch(),
          ),
          IconButton(
            color: Colors.white,
            icon: ValueListenableBuilder(
              valueListenable: cameraController.cameraFacingState,
              builder: (context, state, child) {
                switch (state) {
                  case CameraFacing.front:
                    return const Icon(Icons.camera_front);
                  case CameraFacing.back:
                    return const Icon(Icons.camera_rear);
                }
              },
            ),
            iconSize: 32.0,
            onPressed: () => cameraController.switchCamera(),
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: cameraController,
            onDetect: (capture) {
              if (!isStringFound) {
                final List<Barcode> barcodes = capture.barcodes;
                for (final barcode in barcodes) {
                  final scannedString = barcode.rawValue;
                  handleScannedString(scannedString!);
                }
              }
            },
          ),
          const Text(
            'Scan your log-in code!',
            textAlign: TextAlign.center,
          ),
          Center(
            child: AnimatedOpacity(
              opacity: isDecryptionSuccess ? 1.0 : 0.0,
              duration: const Duration(milliseconds: 800),
              child: const Icon(
                Icons.check_circle_outline_rounded,
                size: 100.0,
                color: Colors.green,
              ),
            ),
          ),
          Center(
            child: AnimatedOpacity(
              opacity: isFailed ? 1.0 : 0.0,
              duration: const Duration(milliseconds: 800),
              child: const Icon(
                Icons.error_outline_rounded,
                size: 100.0,
                color: Colors.red,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
