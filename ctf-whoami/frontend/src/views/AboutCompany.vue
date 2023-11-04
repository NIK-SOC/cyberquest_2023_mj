<template>
  <div class="bg-gray-100 min-h-screen p-8">
    <header class="bg-blue-500 text-white p-4">
      <router-link to="/" class="mr-4 hover:underline">Home</router-link>
      <router-link to="/pricing" class="mr-4 hover:underline">Pricing</router-link>
    </header>
    <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
      <h2 class="text-3xl font-semibold text-blue-500 mb-4">Welcome to Whoami</h2>
      <article class="prose lg:prose-xl lg:prose-xl bg-gray-100">
        <p>Dive into the extraordinary world of pet recognition with Whoami, a cutting-edge creation by Honeylab
          Technologies. TM</p>
        <br class="clear-both" />
        <p>Immerse yourself in a realm where technology meets your pets, where we decode the language of tails, purrs, and
          barks. Whoami isn't just a product; it's a pet lover's dream come true.</p>
        <p>Our revolutionary AI algorithms are on a mission to understand the unique personalities of your cats and dogs.
          With every pawprint and whisker, Whoami becomes a better friend to your furry companions.</p>
        <br class="clear-both" />
        <p>🐾 <strong>Join the Beta Journey:</strong> Whoami is currently in its beta phase, and our AI is eager to learn.
          Be part of this exciting adventure as we train and refine Whoami. Help us shape the future of pet interaction.
        </p>
        <br class="clear-both" />
        <p>🎉 <strong>Subscribe Today:</strong> Unlock the power of Whoami and embark on an enchanting journey with your
          pets. Subscribe now and witness the magic of pet recognition unfold.</p>
        <br class="clear-both" />
        <p>Whoami: Bringing pet owners and their four-legged family members closer than ever before. Discover the
          extraordinary in the ordinary.</p>
        <br class="clear-both" />
        <center>
          <p><strong>This project is currently in a beta phase. In order to get your own API key, you must contact the
              sales team at the Pricing page.</strong></p>
        </center>
      </article>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AboutCompany',
};
</script>
