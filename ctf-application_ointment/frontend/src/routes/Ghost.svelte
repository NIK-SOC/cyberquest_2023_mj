<script>
	import ghost1 from '$lib/assets/ghost1.png';
	import ghost2 from '$lib/assets/ghost2.png';
	import { onMount } from 'svelte';

	const ghostImage = Math.random() < 0.5 ? ghost1 : ghost2;
	const delay = Math.random() * animationDuration;

	const ghostSize = 100;
	const animationDuration = 2;

	let randomX = '0px';
	let randomY = '0px';

	function calculateRandomPosition() {
		if (typeof window !== 'undefined') {
			const viewportWidth = window.innerWidth;
			const viewportHeight = window.innerHeight;

			randomX = `${Math.random() * (viewportWidth - ghostSize)}px`;
			randomY = `${Math.random() * (viewportHeight - ghostSize)}px`;
		}
	}

	onMount(() => {
		calculateRandomPosition();
	});
</script>

<div
	class="ghost"
	style="top: {randomY}; left: {randomX}; background-image: url({ghostImage}); --delay: {delay};"
/>
<div
	class="ghost"
	style="top: {randomY}; left: {randomX}; background-image: url({ghostImage}); --delay: {delay +
		0.2};"
/>

<style>
	.ghost {
		position: absolute;
		width: 100px;
		height: 100px;
		background-size: cover;
		background-color: transparent;
		z-index: -1;
		animation: float 2s ease-in-out infinite;
		animation-delay: calc(var(--delay) * -1s);
		opacity: 0.7;
		filter: drop-shadow(0 0 0.75rem #000000);
	}

	@keyframes float {
		0%,
		100% {
			transform: translate(0, 0);
		}
		25% {
			transform: translate(-5px, 5px);
		}
		50% {
			transform: translate(5px, -5px);
		}
		75% {
			transform: translate(5px, 5px);
		}
	}
</style>
