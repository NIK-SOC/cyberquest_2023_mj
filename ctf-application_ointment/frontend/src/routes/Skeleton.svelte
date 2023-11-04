<script>
	import { isPlaying } from './Stores';
	import { onMount } from 'svelte';
	export let style;
	export let file;

	let danceClass = '';

	onMount(() => {
		isPlaying.subscribe((value) => {
			if (value) {
				danceClass = 'animated';
			} else {
				danceClass = '';
			}
		});
	});
</script>

<img class="skeleton {danceClass}" src={file} alt="Skeleton" {style} />

<style>
	.skeleton {
		position: absolute;
		z-index: -2;
	}

	.animated {
		animation: dance 2s infinite;
	}

	@keyframes dance {
		0%,
		100% {
			transform: rotate(+45deg);
		}
		50% {
			transform: rotate(-45deg);
		}
	}
</style>
