<script lang="ts">
	import { onMount } from 'svelte';
	import { isPlaying } from './Stores';
	let grayscaleValue = 100;
	let increasing = true;
	let bodyBefore = null;
	let isMusicPlaying = false;

	isPlaying.subscribe((value) => {
		isMusicPlaying = value;
	});

	onMount(() => {
		for (let i = 0; i < document.styleSheets.length; i++) {
			if (document.styleSheets[i].href && document.styleSheets[i].href.includes('app.css')) {
				for (let i = 0; i < document.styleSheets[i].cssRules.length; i++) {
					if (document.styleSheets[i].cssRules[i].selectorText === 'body::before') {
						bodyBefore = document.styleSheets[i].cssRules[i];
						break;
					}
				}
				break;
			}
		}
		setInterval(heartbeatEffect, 100);
	});

	function heartbeatEffect() {
		if (increasing) {
			grayscaleValue += 2.5;
			if (grayscaleValue >= 100) {
				increasing = false;
			}
		} else {
			grayscaleValue -= 2.5;
			if (grayscaleValue <= 0) {
				increasing = true;
			}
		}
		if (bodyBefore) {
			bodyBefore.style.filter = `grayscale(${grayscaleValue}%)`;
			if (isMusicPlaying) {
				bodyBefore.style.filter += ' hue-rotate(100deg)';
			}
		}
	}

	import Ghost from './Ghost.svelte';
	import Witch from './Witch.svelte';
	import Skeleton from './Skeleton.svelte';
	import skeleton1 from '$lib/assets/skeleton1.png';
	import skeleton2 from '$lib/assets/skeleton2.png';
	import skeleton3 from '$lib/assets/skeleton3.png';
	import ghost from '$lib/assets/ghost.png';

	let ghosts = [];
	let numGhosts = Math.floor(Math.random() * 3) + 3;
	for (let i = 0; i < numGhosts; i++) {
		ghosts.push(Ghost);
	}

	let skeletonPositions = [
		'top: 10%; left: 15%;',
		'top: 10%; right: 15%;',
		'bottom: 10%; left: 15%;',
		'bottom: 10%; right: 15%;'
	];
</script>

{#each ghosts as GhostComponent}
	<GhostComponent />
{/each}

<Witch />

{#each skeletonPositions as position, index}
	{#if index === 0}
		<Skeleton file={skeleton1} style={position} />
	{:else if index === 1}
		<Skeleton file={skeleton2} style={position} />
	{:else if index === 2}
		<Skeleton file={skeleton3} style={position} />
	{:else if index === 3}
		<Skeleton file={ghost} style={position} />
	{/if}
{/each}
