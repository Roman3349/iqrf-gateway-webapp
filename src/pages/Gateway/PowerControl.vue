<template>
	<div>
		<h1>{{ $t('gateway.power.title') }}</h1>
		<CCard body-wrapper>
			<CButton
				color='danger'
				@click='powerOff()'
			>
				<CIcon :content='icons.off' />
				{{ $t('gateway.power.powerOff') }}
			</CButton> <CButton
				color='primary'
				@click='reboot()'
			>
				<CIcon :content='icons.reboot' />
				{{ $t('gateway.power.reboot') }}
			</CButton>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CIcon} from '@coreui/vue/src';
import {cilPowerStandby, cilReload} from '@coreui/icons';
import GatewayService from '../../services/GatewayService';
import { MetaInfo } from 'vue-meta';
import {Dictionary} from 'vue-router/types/router';
import { AxiosResponse } from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CIcon,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.power.title',
		};
	}
})

/**
 * Power control component
 */
export default class PowerControl extends Vue {
	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		off: cilPowerStandby,
		reboot: cilReload
	}

	/**
	 * Performs power off
	 */
	private powerOff(): void {
		GatewayService.performPowerOff()
			.then((response: AxiosResponse) =>
				this.$toast.info(
					this.$t('gateway.power.messages.powerOffSuccess', {time: this.parseActionTime(response.data.timestamp)}).toString()
				)
			);
	}

	/**
	 * Performs reboot
	 */
	private reboot(): void {
		GatewayService.performReboot()
			.then((response: AxiosResponse) => 
				this.$toast.info(
					this.$t('gateway.power.messages.rebootSuccess', {time: this.parseActionTime(response.data.timestamp)}).toString()
				)
			);
	}

	/**
	 * Converts timestamp to time string
	 */
	private parseActionTime(timestamp: number): string {
		return new Date(timestamp * 1000).toLocaleTimeString();
	}
}
</script>
