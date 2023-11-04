<script lang="ts">
	import Modal, { bind } from 'svelte-simple-modal';
	import { writable } from 'svelte/store';
	import Popup from './Popup.svelte';
	import { isLoggedIn } from './Stores';
	import sadTrombone from '$lib/assets/Sad-trombone.mp3';

	const modal = writable(null);
	var _0x7f9d = [
		'\x65\x67\x6F\x6E',
		'\x72\x61\x79',
		'\x70\x65\x74\x65\x72',
		'\x77\x69\x6E\x73\x74\x6F\x6E',
		'\x67\x68\x6F\x73\x74',
		'\x77\x69\x74\x63\x68',
		'\x6D\x6F\x6E\x73\x74\x65\x72',
		'\x7A\x6F\x6D\x62\x69\x65',
		'\x76\x61\x6D\x70\x69\x72\x65',
		'\x77\x65\x72\x65\x77\x6F\x6C\x66',
		'\x67\x61\x72\x6C\x69\x63',
		'\x73\x70\x69\x64\x65\x72',
		'\x67\x6F\x62\x6C\x69\x6E',
		'\x77\x69\x63\x6B\x65\x64',
		'\x73\x6B\x75\x6C\x6C',
		'\x73\x63\x61\x72\x65\x63\x72\x6F\x77',
		'\x63\x61\x6E\x64\x6C\x65',
		'\x70\x75\x6D\x70\x6B\x69\x6E',
		'\x67\x68\x6F\x75\x6C',
		'\x67\x72\x61\x76\x65\x79\x61\x72\x64',
		'\x73\x6B\x65\x6C\x65\x74\x6F\x6E',
		'\x73\x63\x72\x65\x61\x6D',
		'\x68\x6F\x77\x6C',
		'\x66\x72\x69\x67\x68\x74',
		'\x66\x61\x6E\x67\x73',
		'\x66\x61\x6E\x67',
		'\x63\x65\x6D\x65\x74\x65\x72\x79',
		'\x62\x6C\x6F\x6F\x64',
		'\x62\x61\x74',
		'\x62\x72\x6F\x6F\x6D',
		'\x63\x61\x75\x6C\x64\x72\x6F\x6E',
		'\x62\x6C\x61\x63\x6B\x63\x61\x74'
	];
	const validUsernames = [
		_0x7f9d[0],
		_0x7f9d[1],
		_0x7f9d[2],
		_0x7f9d[3],
		_0x7f9d[4],
		_0x7f9d[5],
		_0x7f9d[6],
		_0x7f9d[7],
		_0x7f9d[8],
		_0x7f9d[9],
		_0x7f9d[10],
		_0x7f9d[11],
		_0x7f9d[12],
		_0x7f9d[13],
		_0x7f9d[14],
		_0x7f9d[15],
		_0x7f9d[16],
		_0x7f9d[17],
		_0x7f9d[18],
		_0x7f9d[19],
		_0x7f9d[20],
		_0x7f9d[21],
		_0x7f9d[22],
		_0x7f9d[23],
		_0x7f9d[24],
		_0x7f9d[25],
		_0x7f9d[26],
		_0x7f9d[27],
		_0x7f9d[28],
		_0x7f9d[29],
		_0x7f9d[30],
		_0x7f9d[31]
	];

	let username = '';
	let error = '';
	export let backendUrl = '';

	function handleInputChange(event: { target: { value: string } }) {
		const newValue = event.target.value;
		if (validUsernames.indexOf(newValue) != -1) console.log('[DEBUG] username seems valid');
	}

	function trollUser() {
		const audio = new Audio(sadTrombone);
		audio.volume = 0.2;
		audio.play();
	}

	async function handleSubmit() {
		if (username.toLowerCase() === 'admin') trollUser();
		const formData = new URLSearchParams();
		formData.append('username', username);

		try {
			const response = await fetch(`${backendUrl}/login`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: formData
			});

			if (!response.ok) {
				const data = await response.json();
				if (data.error) {
					error = data.error;
					// @ts-ignore
					modal.set(bind(Popup, { message: error }));
				}
			} else {
				const data = await response.json();
				const token = data.token;
				const expTime = data.exp;

				localStorage.setItem('token', token);
				localStorage.setItem('exp', expTime);

				isLoggedIn.set(true);
			}
		} catch (err) {
			// @ts-ignore
			modal.set(bind(Popup, { message: err.message }));
		}
	}
</script>

<div class="form">
	<form on:submit={handleSubmit}>
		<label for="username">Username:</label>
		<input type="text" id="username" bind:value={username} on:input={handleInputChange} />
		<button type="submit">Submit</button>
	</form>
</div>

<Modal show={$modal} />
