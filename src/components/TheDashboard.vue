<template>
	<div class='c-app'>
		<TheSidebar />
		<CWrapper>
			<TheHeader />
			<div class='c-body'>
				<main class='c-main'>
					<CContainer fluid>
						<div class='fade-in'>
							<slot />
							<router-view />
						</div>
					</CContainer>
				</main>
			</div>
		</CWrapper>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CWrapper} from '@coreui/vue/src';
import TheHeader from './TheHeader.vue';
import TheSidebar from './TheSidebar.vue';

@Component({
	components: {
		CContainer,
		CWrapper,
		TheHeader,
		TheSidebar,
	},
})

/**
 * Dashboard component
 */
export default class TheDashboard extends Vue {}
</script>
