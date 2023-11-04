<template>
    <div class="bg-gray-100 min-h-screen p-8">
        <header class="bg-blue-500 text-white p-4">
            <router-link to="/pricing" class="mr-4 hover:underline">Pricing</router-link>
            <router-link to="/about" class="mr-4 hover:underline">About</router-link>
        </header>
        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4"
            role="alert">
            <strong class="font-bold">Error!</strong>
            <br />
            <span class="block sm:inline">{{ errorMessage }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg @click="errorMessage = null" class="fill-current h-6 w-6 text-red-500" role="button"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1 1 0 0 1-1.414 0L10 11.414l-2.93 2.93a1 1 0 1 1-1.414-1.414l2.93-2.93-2.93-2.93a1 1 0 1 1 
                    1.414-1.414l2.93 2.93 2.93-2.93a1 1 0 1 1 1.414 1.414l-2.93 2.93 2.93 2.93a1 1 0 0 1 0 1.414z" />
                </svg>
            </span>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg mt-4">
            <h2 class="text-2xl font-semibold mb-4">API Explorer</h2>

            <div class="rounded-lg p-4 mb-4 bg-green-200">
                <div class="flex items-center justify-between">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-green-500 text-white font-semibold rounded-lg">
                        GET</div>
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold">/image</h3>
                        <button @click="toggleGetDetails"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 ml-4">Toggle</button>
                    </div>
                </div>
                <div v-if="showGetDetails" class="bg-white rounded p-4 mt-2">
                    <p class="text-gray-600">Returns a random cat or dog image from the trained dataset.</p>
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold">Request Headers</h4>
                        <ul class="list-disc ml-4">
                            <li>Content-Type: application/json</li>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold">Response Types</h4>
                        <ul class="list-disc ml-4">
                            <li>Image/jpeg</li>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <a v-if="imageData" :href="imageData" download="image.jpg">
                            <img v-if="imageData" :src="imageData" class="w-64 mx-auto shadow-lg rounded" />
                        </a>
                    </div>
                    <button @click="tryGetRequest"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mt-4 float-right">
                        Try It
                    </button>
                    <br class="clear-both" />
                </div>
            </div>

            <div class="rounded-lg p-4 mb-4 bg-purple-200">
                <div class="flex items-center justify-between">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-purple-500 text-white font-semibold rounded-lg">
                        POST</div>
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold">/classify</h3>
                        <button @click="togglePostDetails"
                            class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 ml-4">Toggle</button>
                    </div>
                </div>
                <div v-if="showPostDetails" class="bg-white rounded p-4 mt-2">
                    <p class="text-gray-600">Determine whether the supplied image is a dog or a cat using superior AI
                        technology. Returns undetermined in any other case.</p>
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold">Paid API</h4>
                        <p class="text-gray-600">This API endpoint is only available to our paying users. A demo that can
                            only be used on this site is available below.</p>
                        <router-link to="/pricing" class="text-blue-500">Upgrade to Pro</router-link>
                    </div>
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold">Request Headers</h4>
                        <ul class="list-disc ml-4">
                            <li>Content-Type: multipart/form-data</li>
                            <li>Authorization: Bearer {{ apiKey }}</li>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold">Response Types</h4>
                        <ul class="list-disc ml-4">
                            <li>JSON</li>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <input v-model="apiKey" class="w-full border rounded p-2" placeholder="Enter API Key" />
                    </div>
                    <input type="file" ref="imageInput" class="mt-4" accept="image/*" @change="handleImageChange" />
                    <label v-if="selectedImage && selectedImage.type !== 'image/jpeg'"
                        class="text-red-500 text-sm mt-2">Only JPEG images are supported</label>
                    <div v-if="classificationResult" class="mt-4">
                        <h4 class="text-lg font-semibold">Classification Result</h4>
                        <p class="text-gray-600">The image is <span class="font-semibold">{{ classificationResult }}</span>.
                        </p>
                    </div>
                    <button @click="tryPostRequest" :disabled="tryOutPostDisabled"
                        class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mt-4 float-right"
                        :class="{ 'opacity-50 cursor-not-allowed': tryOutPostDisabled }">
                        Try It
                    </button>
                    <br class="clear-both" />
                </div>
            </div>
        </div>
    </div>
</template>
  
  
<script>
export default {
    data() {
        return {
            showGetDetails: false,
            showPostDetails: false,
            apiKey: '',
            selectedImage: null,
            classificationResult: null,
            imageData: null,
            tryOutPostDisabled: true,
            errorMessage: null,
        };
    },
    methods: {
        toggleGetDetails() {
            this.showGetDetails = !this.showGetDetails;
            this.showPostDetails = false;
            this.errorMessage = null;
        },
        togglePostDetails() {
            this.showPostDetails = !this.showPostDetails;
            this.showGetDetails = false;
            this.errorMessage = null;
            this.classificationResult = null;
        },
        async tryGetRequest() {
            try {
                const response = await this.$http.get('/image', { responseType: 'arraybuffer' });
                const blob = new Blob([response.data], { type: 'image/jpeg' });
                const imageUrl = URL.createObjectURL(blob);
                this.imageData = imageUrl;
                this.errorMessage = null;
            } catch (error) {
                console.error('Error fetching random image:', error);
                this.errorMessage = error.message;
            }
        },
        async tryPostRequest() {
            const formData = new FormData();
            formData.append('image', this.selectedImage);

            const config = {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            };

            try {
                let response;
                if (this.apiKey) {
                    config.headers['Authorization'] = `Bearer ${this.apiKey}`;
                    response = await this.$http.post('/classify', formData, config);
                } else {
                    config.headers['X-Playground'] = 'true';
                    const proxyURL = `/proxy?url=http://${this.siteDomain}/classify`;
                    response = await this.$proxy.post(proxyURL, formData, config);
                }

                this.classificationResult = response.data;
                this.errorMessage = null;
            } catch (error) {
                console.error('Error classifying image:', error);
                this.errorMessage = error.message;
                if (error.response) {
                    this.errorMessage += `: - ${error.response.data}`;
                }
                this.classificationResult = null;
            }
        },
        handleImageChange(event) {
            this.selectedImage = event.target.files[0];
            this.tryOutPostDisabled = this.selectedImage === null || this.selectedImage.type !== 'image/jpeg';
        },
    },
};
</script>
  
<style scoped>
.bg-gray-100 {
    min-height: 100vh;
    min-width: 60vw;
}
</style>
  