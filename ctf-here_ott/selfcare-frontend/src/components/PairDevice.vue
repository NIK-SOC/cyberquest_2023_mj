<template>
    <div id="pair" class="pair-device-container">
      <h2>Pair Device</h2>
      <div v-if="qrCode" class="qr-code-container">
        <img :src="qrCode" @click="() => pd(serial)" alt="QR Code" class="qr-code" />
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import config from '../Config.js';
  import { getSerial, getUid, sign } from '../Device.js';
  import { useToast } from "vue-toastification";
  
  export default {
    setup() {
      const toast = useToast();
      return { toast };
    },
    data() {
      return {
        qrCode: null,
        serial: null
      };
    },
    async mounted() {
      this.serial = getSerial();
      this.pd(this.serial);
    },
    methods: {
      pd(serial) {
        const backendUrl = config.ServerUrl;
        const headers = {
          SerialNumber: serial,
          uuid: this.generateUUID(),
          uid: getUid().toString(),
          sig: sign(`${backendUrl}/device/pair`, "").toString(),
        };
        const login = {
          username: 'hereottselfcare',
          password: 'hereottselfcare',
        };
  
        axios
          .post(`${backendUrl}/device/pair`, {}, { headers, auth: login })
          .then((response) => {
            this.qrCode = 'data:image/png;base64,' + response.data.qrCode;
          })
          .catch((error) => {
            console.error('Error pairing device:', error);
            this.toast.error("Error pairing");
          });
      },
      generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
          const r = (Math.random() * 16) | 0;
          const v = c == 'x' ? r : (r & 0x3) | 0x8;
          return v.toString(16);
        });
      },
    },
  };
  </script>
  
  <style scoped>
  .pair-device-container {
    text-align: center;
    padding: 20px;
  }
  
  .qr-code-container {
    margin-top: 20px;
  }
  
  .qr-code {
    max-width: 100%;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: transform 0.2s ease;
  }
  
  .qr-code:hover {
    transform: scale(1.05);
  }
  </style>
  