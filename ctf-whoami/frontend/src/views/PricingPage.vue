<template>
    <div class="bg-gray-100 min-h-screen p-8">
        <header class="bg-blue-500 text-white p-4">
            <router-link to="/" class="mr-4 hover:underline">Home</router-link>
            <router-link to="/about" class="mr-4 hover:underline">About</router-link>
        </header>
        <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Pricing</h2>
            <div class="flex justify-between">
                <div class="w-48 p-4 rounded-lg bg-blue-200">
                    <h3 class="text-lg font-semibold text-blue-500 mb-2">Basic Plan</h3>
                    <p class="text-gray-600 mb-2">Perfect for small teams and individuals.</p>
                    <p class="text-2xl font-semibold text-blue-500">$9.99/mo</p>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-2"
                        @click="getStarted">Contact us</button>
                </div>
                <div class="w-48 p-4 rounded-lg bg-purple-200">
                    <h3 class="text-lg font-semibold text-purple-500 mb-2">Pro Plan</h3>
                    <p class="text-gray-600 mb-2">Ideal for businesses and large teams.</p>
                    <p class="text-2xl font-semibold text-purple-500">$29.99/mo</p>
                    <button class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mt-2"
                        @click="getStarted">Contact us</button>
                </div>
            </div>
        </div>
    </div>
</template>
  
<script>
export default {
    name: 'PricingPage',
    methods: {
        getStarted() {
            window.location.href = 'mailto:whoami@honeylab.hu';
        }
    }
};
</script>

  