<script lang="ts">
	import Main from './Main.svelte';
	import Effects from './Effects.svelte';

	const backend_port = 1337;
	let isJavaScriptEnabled = false;
	let backendUrl = '';
	if (typeof window !== 'undefined') {
		backendUrl = `${window.location.protocol}//${window.location.hostname}:${backend_port}`;
		isJavaScriptEnabled = true;
		console.log(`[DEBUG] Backend URL: ${backendUrl}`);
	} else {
		console.log('This page only works in the browser');
	}
	console.log(
		"[NOTE] The witches sometimes like to glitch. Let's be honest, they just like to party hard. BWAHAHAHA"
	);
</script>

<div class="container">
	{#if !isJavaScriptEnabled}
		<div class="alert alert-danger" role="alert">
			<h4 class="alert-heading">JavaScript is disabled!</h4>
			<p>
				This page requires JavaScript to be enabled in order to work properly. Please enable
				JavaScript and refresh the page.
			</p>
		</div>
	{:else}
		<Main {backendUrl} />
	{/if}
</div>

<Effects />
