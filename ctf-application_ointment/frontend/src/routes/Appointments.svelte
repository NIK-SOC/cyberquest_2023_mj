<script>
	import { onMount } from 'svelte';
	export let backendUrl = '';
	import Modal, { bind } from 'svelte-simple-modal';
	import { writable } from 'svelte/store';
	import Popup from './Popup.svelte';
	import Fa from 'svelte-fa/src/fa.svelte';
	import { faArrowAltCircleLeft } from '@fortawesome/free-solid-svg-icons';
	import { isLoggedIn } from './Stores';
	const modal = writable(null);

	/**
	 * @type {string | any[]}
	 */
	let appointments = [];
	/**
	 * @type {null}
	 */
	let selectedAppointment = null;

	onMount(async () => {
		try {
			const response = await fetch(`${backendUrl}/appointments`, {
				headers: {
					Authorization: `Bearer ${localStorage.token}`
				}
			});

			if (response.ok) {
				const data = await response.json();

				appointments = Object.entries(data.appointments)
					.map(([date, name]) => ({
						date: parseInt(name.date, 10) * 1000,
						name
					}))
					.sort((a, b) => a.date - b.date);
			} else {
				console.error('Error fetching appointments:', response.statusText);
			}
		} catch (error) {
			console.error('Error:', error);
		}
	});

	function goBack() {
		localStorage.removeItem('token');
		localStorage.removeItem('exp');
		isLoggedIn.set(false);
	}

	/**
	 * @param {{ preventDefault: () => void; }} event
	 */
	async function handleFormSubmit(event) {
		event.preventDefault();

		if (selectedAppointment === null) {
			console.log('No appointment selected');
			// @ts-ignore
			modal.set(bind(Popup, { message: 'No appointment selected' }));
			return;
		}

		try {
			const response = await fetch(`${backendUrl}/appointment`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: `Bearer ${localStorage.token}`
				},
				body: JSON.stringify({ appointment: selectedAppointment })
			});

			if (response.ok) {
				console.log('Appointment successfully sent to the backend');
				// @ts-ignore
				modal.set(bind(Popup, { message: 'Successfully registered!' }));
			} else {
				console.error('Error sending appointment to the backend:', response.statusText);
				// @ts-ignore
				modal.set(bind(Popup, { message: response.statusText }));
			}
		} catch (error) {
			console.error('Error:', error);
		}
	}
</script>

<Modal show={$modal} />

<div>
	<!-- svelte-ignore a11y-invalid-attribute -->
	<a href="#" on:click={goBack}>
		<Fa icon={faArrowAltCircleLeft} size="2x" color="gray" />
	</a>
	<h2>Appointments</h2>
	{#if appointments.length > 0}
		<form on:submit={handleFormSubmit}>
			{#each appointments as appointment}
				<label>
					<input
						type="radio"
						name="appointment"
						on:change={() => (selectedAppointment = appointment.date)}
						value={appointment.date}
					/>
					{new Date(appointment.date).toLocaleString()} - {appointment.name.name}
				</label>
				<br />
			{/each}
			<button type="submit">Submit</button>
		</form>
	{:else}
		<p>No appointments available.</p>
	{/if}
</div>
