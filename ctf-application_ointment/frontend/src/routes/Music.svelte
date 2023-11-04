<script>
	import { onMount } from 'svelte';
	import { faCompactDisc } from '@fortawesome/free-solid-svg-icons';
	import Fa from 'svelte-fa/src/fa.svelte';
	import music from '$lib/assets/music.mp3';
	import { isPlaying } from './Stores';

	let isExpanded = false;

	function toggleExpansion(event) {
		const audio = document.querySelector('audio');
		if (event && event.type === 'keypress') {
			if (event.key === ' ') {
				event.preventDefault();
				if (!isExpanded) isExpanded = true;
				if (audio.paused) {
					audio.play();
				} else {
					audio.pause();
				}
			}
			if (event.key !== 'Enter') {
				return;
			}
		}
		if (!isExpanded) {
			audio.play();
			const infoTab = document.querySelector('.info-tab');
			infoTab.style.backgroundColor = '#ffffff';
		}
		isExpanded = !isExpanded;
	}

	function setColorToGray() {
		if (isExpanded) {
			return;
		}
		const infoTab = document.querySelector('.info-tab');
		infoTab.style.backgroundColor = '#cccccc';
	}

	function setColorToWhite() {
		if (isExpanded) {
			return;
		}
		const infoTab = document.querySelector('.info-tab');
		infoTab.style.backgroundColor = '#ffffff';
	}

	onMount(() => {
		document.addEventListener('click', (event) => {
			if (isExpanded && !event.target.closest('.info-tab')) {
				toggleExpansion();
			}
		});
		document.querySelector('audio').addEventListener('playing', () => {
			isPlaying.set(true);
		});
		document.querySelector('audio').addEventListener('pause', () => {
			isPlaying.set(false);
		});
	});
</script>

<div
	class="info-tab"
	class:collapsed={!isExpanded}
	on:click={toggleExpansion}
	on:keypress={toggleExpansion}
	on:mouseenter={setColorToGray}
	on:mouseleave={setColorToWhite}
	role="button"
	tabindex="0"
>
	<div class="music-icon">
		<Fa icon={faCompactDisc} size="2x" spin />
	</div>
	<div>
		<div class="music-player" class:expanded={isExpanded}>
			<p>Song: NIVIRO - Annabelle's Tea Party [NCS Release]</p>
			<p>Music provided by NoCopyrightSounds Free</p>
			<audio controls loop>
				<source src={music} type="audio/mpeg" />
				Your browser does not support the audio element.
			</audio>
		</div>
	</div>
</div>

<style>
	.info-tab {
		position: fixed;
		bottom: 20px;
		right: 0;
		background-color: #ffffff;
		border-radius: 10px 0 0 10px;
		padding: 10px;
		box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
		cursor: pointer;
		display: flex;
		align-items: center;
		transition: max-width 0.3s ease;
		max-width: 300px;
		overflow: hidden;
	}

	.info-tab.collapsed {
		right: 0;
		width: 40px;
		background-color: #ffffff;
		transition: right 0.5s ease, width 0.5s ease, background-color 0.3s ease;
	}

	.music-icon {
		margin-right: 8px;
	}

	.music-player {
		flex-direction: column;
		align-items: center;
		visibility: hidden;
		height: 0;
		overflow: hidden;
		transition: visibility 0.3s, height 0.3s;
	}

	.music-player.expanded {
		visibility: visible;
		height: auto;
	}

	audio {
		width: 100%;
		transition: height 0.3s ease;
	}
</style>
