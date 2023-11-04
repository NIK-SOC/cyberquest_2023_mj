<template>
    <div id="list" class="device-list-container">
      <h2>Device List</h2>
      <ul class="device-list">
        <li v-for="device in devices" :key="device.id" class="device-item">
          <div class="device-info">
            <span class="device-name">{{ device.name }}</span>
            <span class="device-type">Type: {{ device.type }}</span>
          </div>
          <button @click="rd(device.id)" :disabled="idr(device.type)" class="remove-button">Remove</button>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import config from '../Config.js';
  import { getSerial, sign, getUid } from '../Device.js';
  import { useToast } from "vue-toastification";
  
  export default {
    setup() {
      const toast = useToast();
      return { toast };
    },
    data() {
      return {
        devices: [],
      };
    },
    async mounted() {
      const serial = getSerial();
      this.fdl(serial);
    },
    methods: {
      fdl(serial) {
        const backendUrl = config.ServerUrl;
        const headers = {
          SerialNumber: serial,
          uuid: this.generateUUID(),
          uid: getUid().toString(),
          sig: sign(`${backendUrl}/devices`, "").toString(),
        };
        const login = {
          username: 'hereottselfcare',
          password: 'hereottselfcare',
        };
  
        axios
          .get(`${backendUrl}/devices`, { headers, auth: login })
          .then((response) => {
            this.devices = response.data['devices'];
          })
          .catch((error) => {
            console.error('Error fetching devices:', error);
            this.toast.error("Error fetching devices");
          });
      },
      generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
          const r = (Math.random() * 16) | 0,
            v = c == 'x' ? r : (r & 0x3) | 0x8;
          return v.toString(16);
        });
      },
      idr(deviceType) {
        return config.NotAllowedToRemoveDeviceTypes.includes(deviceType);
      },
      rd(deviceId) {
        console.log(`Removing device with ID ${deviceId}`);
        const backendUrl = config.ServerUrl;
        const headers = {
          SerialNumber: getSerial(),
          uuid: this.generateUUID(),
          uid: getUid().toString(),
          sig: sign(`${backendUrl}/device/delete`, "").toString(),
        }
        const login = {
          username: 'hereottselfcare',
          password: 'hereottselfcare',
        }
        const params = {
          id: deviceId
        }
        axios
          .delete(`${backendUrl}/device/delete`, { headers, params, auth: login })
          .then((response) => {
            this.devices = response.data['devices'];
          })
          .catch((error) => {
            console.error('Error removing device:', error);
            this.toast.error("Error removing device");
          });
      },
    },
  };
  </script>
  
  <style scoped>
.device-list-container {
  text-align: center;
  padding: 20px;
}

.device-list {
  list-style: none;
  padding: 0;
}

.device-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f0f0f0;
  border: 1px solid #ddd;
  padding: 10px;
  margin: 10px 0;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.device-item:hover {
  background-color: #e0e0e0;
}

.device-info {
  flex-grow: 1;
  text-align: left;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.device-name {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 5px;
}

.device-type {
  background-color: #4caf50;
  color: #fff;
  font-size: 14px;
  padding: 5px 10px;
  border-radius: 5px;
  display: inline-block;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
  transition: background-color 0.3s ease;
}


.remove-button {
  background-color: #ff6b6b;
  color: #fff;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.remove-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.remove-button:hover {
  background-color: #ff4040;
}

.back-link {
  text-decoration: none;
  color: #333;
  font-size: 18px;
  margin-top: 20px;
  display: inline-block;
  transition: color 0.3s ease;
}

.back-link:hover {
  color: #007bff;
}
</style>