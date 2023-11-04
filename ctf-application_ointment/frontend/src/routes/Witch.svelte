<script>
	import { onMount, onDestroy } from 'svelte';

	let witches = [];

	let animationFrame = null;

	let witchWidth = 100;
	let witchHeight = 100;
	const witchSpeed = 2;

	let witchIdCounter = 0;
	function generateUniqueWitchId() {
		return witchIdCounter++;
	}

	function createWitch(imageUrl, screenWidth, screenHeight) {
		const minX = 0;
		const minY = 0;
		const maxX = screenWidth - witchWidth;
		const maxY = screenHeight - witchHeight;

		const x = Math.random() * (maxX - minX) + minX;
		const y = Math.random() * (maxY - minY) + minY;

		return {
			id: generateUniqueWitchId(),
			x,
			y,
			dx: witchSpeed * (Math.random() > 0.5 ? 1 : -1),
			dy: witchSpeed * (Math.random() > 0.5 ? 1 : -1),
			imageUrl,
			rotation: 0
		};
	}

	function updateWitches(screenWidth, screenHeight) {
		witches = witches.map((witch) => {
			const oldDx = witch.dx;
			const oldDy = witch.dy;

			witch.x += witch.dx;
			witch.y += witch.dy;

			if (witch.x < 0 || witch.x + witchWidth > screenWidth) {
				witch.dx *= -1;
			}
			if (witch.y < 0 || witch.y + witchHeight > screenHeight) {
				witch.dy *= -1;
			}

			witches.forEach((otherWitch) => {
				if (witch !== otherWitch) {
					if (
						witch.x < otherWitch.x + witchWidth &&
						witch.x + witchWidth > otherWitch.x &&
						witch.y < otherWitch.y + witchHeight &&
						witch.y + witchHeight > otherWitch.y
					) {
						witch.dx *= -1;
						witch.dy *= -1;
					}
				}
			});

			if (witch.dx !== oldDx || witch.dy !== oldDy) {
				witch.rotation = calculateRotation(witch.dx, witch.dy);
			}

			return witch;
		});
	}

	function calculateRotation(dx, dy) {
		let angle = Math.atan2(dy, dx) * (180 / Math.PI) + 360;

		if (angle < 0) {
			angle += 360;
		} else if (angle >= 360) {
			angle -= 360;
		}

		return angle;
	}

	function animate(screenWidth, screenHeight) {
		updateWitches(screenWidth, screenHeight);
		animationFrame = requestAnimationFrame(() => animate(screenWidth, screenHeight));
	}

	onMount(async () => {
		if (typeof window !== 'undefined') {
			const witchImageImports = [
				import('$lib/assets/witch1.png'),
				import('$lib/assets/witch2.png'),
				import('$lib/assets/witch3.png'),
				import('$lib/assets/witch4.png')
			];

			const witchImages = await Promise.all(witchImageImports);
			witches = witchImages.map((image) =>
				createWitch(image.default, window.innerWidth, window.innerHeight)
			);
			animate(window.innerWidth, window.innerHeight);
		}
	});

	let resizeListener = null;
	onMount(() => {
		if (typeof window !== 'undefined') {
			resizeListener = () => {
				witchWidth = 100;
				witchHeight = 100;
				const screenWidth = window.innerWidth;
				const screenHeight = window.innerHeight;

				witches = witches.map((witch) => {
					if (witch.x + witchWidth > screenWidth) {
						witch.x = screenWidth - witchWidth;
					}
					if (witch.y + witchHeight > screenHeight) {
						witch.y = screenHeight - witchHeight;
					}
					return witch;
				});
			};

			window.addEventListener('resize', resizeListener);
		}
	});

	onDestroy(() => {
		if (typeof window !== 'undefined') {
			window.removeEventListener('resize', resizeListener);
			if (animationFrame) {
				cancelAnimationFrame(animationFrame);
			}
		}
	});
</script>

<div>
	{#each witches as witch, index}
		<div
			class="witch"
			style="left: {witch.x}px; top: {witch.y}px; background-image: url({witch.imageUrl}); width: {witchWidth}px; height: {witchHeight}px; transform: rotate({calculateRotation(
				witch.dx,
				witch.dy
			)}deg);"
		/>
	{/each}
</div>

<style>
	.witch {
		position: absolute;
		z-index: -2;
		background-size: contain;
	}
</style>
