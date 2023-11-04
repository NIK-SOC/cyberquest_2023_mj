<script lang="ts">
	import Login from './Login.svelte';
	import Appointments from './Appointments.svelte';
	import Music from './Music.svelte';
	import { onMount } from 'svelte';
	import { isLoggedIn } from './Stores';
	export let backendUrl = '';

	const checkLoginStatus = () => {
		if (
			typeof window !== 'undefined' &&
			localStorage.exp &&
			localStorage.exp > (Date.now() / 1000).toString()
		) {
			isLoggedIn.set(true);
		}
	};
	onMount(checkLoginStatus);
</script>

{#if $isLoggedIn}
	<Appointments {backendUrl} />
{:else}
	<Login {backendUrl} />
{/if}

<Music />
