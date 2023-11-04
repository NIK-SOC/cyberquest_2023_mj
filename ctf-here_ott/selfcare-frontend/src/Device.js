import initSync, { gen_serial, sign as Sign, get_uid } from './wasm/native_api';

let wasmModule;
let serial;

const initializeDeviceApi = async () => {
  wasmModule = await initSync();
  const storedSerial = localStorage.getItem('serial');
  if (storedSerial) {
    serial = storedSerial;
  } else {
    serial = generateSerial();
    localStorage.setItem('serial', serial);
  }
};

export const generateSerial = () => {
  if (!wasmModule) {
    throw new Error('DeviceApi is not initialized');
  }
  return gen_serial();
};

export const getSerial = () => {
  if (!serial) {
    throw new Error('Serial is not available');
  }
  return serial;
};

export const sign = (url, post_data) => {
  if (!wasmModule) {
    throw new Error('DeviceApi is not initialized');
  }
  return Sign(url, post_data);
};

export const getUid = () => {
  if (!wasmModule) {
    throw new Error('DeviceApi is not initialized');
  }
  return get_uid();
};

await initializeDeviceApi();
