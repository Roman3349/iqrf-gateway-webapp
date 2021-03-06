<template>
	<div>
		<h1>{{ $t('config.daemon.interfaces.title') }}</h1>
		<CCard body-wrapper>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.interfaces.messages.fetchFailed') }}
			</CElementCover>
			<CSelect
				v-else
				:value.sync='iqrfInterface'
				:label='$t("config.daemon.interfaces.form.interface")'
				:options='interfaceSelect'
				:placeholder='$t("config.daemon.interfaces.form.placeholder")'
				@change='changeInterface'
			/>
		</CCard>
		<CCard v-if='iqrfInterface === "noInterface"' body-wrapper>
			{{ $t('config.daemon.interfaces.messages.noInterface') }}
		</CCard>
		<div v-if='!loadFailed'>
			<IqrfSpi v-if='iqrfInterface === "iqrf::IqrfSpi"' @fetched='configFetched' />
			<IqrfCdc v-if='iqrfInterface === "iqrf::IqrfCdc"' @fetched='configFetched' />
			<IqrfUart v-if='iqrfInterface === "iqrf::IqrfUart"' @fetched='configFetched' />
			<IqrfDpa @fetched='configFetched' />
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CSelect} from '@coreui/vue/src';
import IqrfSpi from '../../components/Config/IqrfSpi.vue';
import IqrfCdc from '../../components/Config/IqrfCdc.vue';
import IqrfUart from '../../components/Config/IqrfUart.vue';
import IqrfDpa from '../../components/Config/IqrfDpa.vue';

import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

import {AxiosError, AxiosResponse} from 'axios';
import {IChangeComponent, IComponent, IConfigFetch} from '../../interfaces/daemonComponent';
import {IOption} from '../../interfaces/coreui';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CSelect,
		IqrfCdc,
		IqrfDpa,
		IqrfSpi,
		IqrfUart
	},
	metaInfo: {
		title: 'config.daemon.interfaces.title'
	}
})

/**
 * Interfaces config page component
 */
export default class Interfaces extends Vue {
	/**
	 * @var {boolean} powerUser Indicates whether the user account is advanced 
	 */
	private powerUser = false;

	/**
	 * @constant {Array<IOption>} interfaceSelect Array of CoreUI select options for interface
	 */
	private interfaceSelect: Array<IOption> = [
		{
			value: 'iqrf::IqrfCdc',
			label: this.$t('config.daemon.interfaces.types.cdc').toString(),
		},
		{
			value: 'iqrf::IqrfSpi',
			label: this.$t('config.daemon.interfaces.types.spi').toString(),
		},
		{
			value: 'iqrf::IqrfUart',
			label: this.$t('config.daemon.interfaces.types.uart').toString(),
		},
	]

	/**
	 * @var {string} iqrfInterface Active interface
	 */
	private iqrfInterface = ''

	/**
	 * @var {Array<IComponent>} iqrfInterfaces Array of IQRF communication interfaces
	 */
	private iqrfInterfaces: Array<IComponent> = []

	/**
	 * @constant {Array<string>} whitelist Array of IQRF communication component names
	 */
	private whitelist: Array<string> = [
		'iqrf::IqrfCdc',
		'iqrf::IqrfSpi',
		'iqrf::IqrfUart',
	]

	/**
	 * @var {Array<string>} children Array of components loading configuration
	 */
	private children: Array<string> = [
		'iface',
		'iqrfDpa'
	]

	/**
	 * @var {Array<string>} failed Array of components that failed configuration fetch
	 */
	private failed: Array<string> = []

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves list of components
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isActive']) {
			this.$store.commit('spinner/SHOW');
		}
		return DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.storeInterfaces(response.data.components);
				this.findActiveInterface();
			})
			.catch((error: AxiosError) => {
				this.loadFailed = true;
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Stores IQRF communication interfaces
	 * @param {Array<IComponent>} components Array of all daemon components
	 */
	private storeInterfaces(components: Array<IComponent>): void {
		let interfaces: Array<IComponent> = [];
		for (const component of components) {
			if (this.whitelist.includes(component.name)) {
				interfaces.push(component);
			}
		}
		this.iqrfInterfaces = interfaces;
	}

	/**
	 * Sets active interface
	 */
	private findActiveInterface(): void {
		for (const component of this.iqrfInterfaces) {
			if (component.enabled) {
				this.iqrfInterface = component.name;
				return;
			}
		}
		this.iqrfInterface = 'noInterface';
	}

	/**
	 * Disables all enabled communication interfaces and enables interface selected by user
	 */
	private changeInterface(): void {
		let updateInterfaces: Array<IChangeComponent> = [];
		for (let component of this.iqrfInterfaces) {
			if (component.name !== this.iqrfInterface && component.enabled) {
				updateInterfaces.push({name: component.name, enabled: false});
			} else if (component.name === this.iqrfInterface && !component.enabled) {
				updateInterfaces.push({name: component.name, enabled: true});
			}
		}
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.changeComponent(updateInterfaces)
			.then(() => {
				this.getConfig().then(() => 
					this.$toast.success(
						this.$t('config.daemon.interfaces.messages.updateSuccess', {interface: this.interfaceCode(this.iqrfInterface)}).toString()
					)
				);
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('config.daemon.interfaces.messages.updateFailed').toString()
				);
			});
	}

	/**
	 * Returns interface abbreviation from component name
	 * @param {string} iqrfInterface Iqrf interface component name
	 * @returns {string} Interface abbreviation
	 */
	private interfaceCode(iqrfInterface: string): string {
		if (iqrfInterface === 'iqrf::IqrfCdc') {
			return 'CDC';
		} else if (iqrfInterface === 'iqrf::IqrfSpi') {
			return 'SPI';
		} else if (iqrfInterface === 'iqrf::IqrfUart') {
			return 'UART';
		}
		return '';
	}

	/**
	 * Handles configuration fetch events
	 */
	private configFetched(data: IConfigFetch): void {
		if (['iqrfCdc', 'iqrfSpi', 'iqrfUart'].includes(data.name)) {
			this.children = this.children.filter((item: string) => item !== 'iface');
		} else {
			this.children = this.children.filter((item: string) => item !== data.name);
		}
		if (!data.success) {
			this.failed.push(this.$t('config.daemon.' + data.name + '.title').toString());
		}
		if (this.children.length > 0) {
			return;
		}
		this.$store.commit('spinner/HIDE');
		if (this.failed.length === 0) {
			return;
		}
		this.$toast.error(
			this.$t(
				'config.daemon.messages.configFetchFailed',
				{children: this.failed.sort().join(', ')},
			).toString()
		);
		this.failed = [];
	}

}
</script>
