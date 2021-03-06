<template>
	<div>
		<h1>{{ $t('network.wireguard.title') }}</h1>
		<CCard>
			<CCardHeader class='border-0'>
				{{ $t('network.wireguard.tunnels.title') }}
				<CButton
					style='float: right;'
					color='success'
					size='sm'
					to='/network/vpn/add'
				>
					<CIcon :content='icons.add' size='sm' />
					{{ $t('forms.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='tableFields'
					:items='tunnels'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('network.wireguard.tunnels.table.noTunnels') }}
					</template>
					<template #state='{item}'>
						<td>
							<CBadge
								:color='item.active ? "success" : "danger"'
							>
								{{ $t('network.wireguard.tunnels.table.states.' + (item.active ? 'active' : 'inactive')) }}
							</CBadge>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								:color='item.active ? "danger" : "success"'
								@click='changeActiveState(item.id, item.name, (item.active ? false : true))'
							>
								<CIcon 
									:content='item.active ? icons.deactivate : icons.activate'
									size='sm'
								/>
								{{ $t('network.wireguard.tunnels.table.action.' + (item.active ? "deactivate" : "activate")) }}
							</CButton> <CButton
								size='sm'
								:color='item.enabled ? "danger" : "success"'
								@click='changeEnabledState(item.id, item.name, (item.enabled ? false : true))'
							>
								<CIcon
									:content='item.enabled ? icons.disable : icons.enable'
									size='sm'
								/>
								{{ $t('table.actions.' + (item.enabled ? "disable" : "enable")) }}
							</CButton> <CButton
								size='sm'
								color='primary'
								:to='"/network/vpn/edit/" + item.id'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								size='sm'
								color='danger'
								@click='removeTunnel(item.id, item.name)'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CInput} from '@coreui/vue/src';
import {cilLink, cilLinkBroken, cilPlus, cilPencil, cilSync, cilTrash, cilXCircle} from '@coreui/icons';

import WireguardService from '../../services/WireguardService';

import {AxiosResponse} from 'axios';
import {IField} from '../../interfaces/coreui';
import {IWG} from '../../interfaces/network';
import {Dictionary} from 'vue-router/types/router';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CInput,
	},
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * Wireguard connections component
 */
export default class WireguardTunnels extends Vue {

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		activate: cilLink,
		deactivate: cilLinkBroken,
		enable: cilSync,
		disable: cilXCircle,
	}

	/**
	 * @var {Array<IWG>} tunnels Array of existing tunnels
	 */
	private tunnels: Array<IWG> = []

	/**
	 * @constant {Array<IField>} tableField Array of CoreUI data table fields
	 */
	private tableFields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('network.wireguard.tunnels.table.name'),
		},
		{
			key: 'state',
			label: this.$t('network.wireguard.tunnels.table.state'),
			sorter: false,
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	]

	/**
	 * Retrieves existing Wireguard tunnels
	 */
	mounted(): void {
		this.getTunnels();
	}

	/**
	 * Retrieves existing Wireguard tunnels and stores data into table
	 */
	private getTunnels(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return WireguardService.listTunnels()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.tunnels = response.data;
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.wireguard.tunnels.messages.listFailed').toString()
				);
			});
	}

	/**
	 * Changes active state of Wireguard tunnel
	 * @param {number} id Wireguard tunnel ID
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private changeActiveState(id: number, name: string, state: boolean): void {
		this.$store.commit('spinner/SHOW');
		if (state) {
			WireguardService.activateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch(() => this.handleActiveError(name, state));
		} else {
			WireguardService.deactivateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch(() => this.handleActiveError(name, state));
		}
	}

	/**
	 * Handles tunnel activation success
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleActiveSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? '' : 'de') + 'activateSuccess',
				{tunnel: name}
			).toString()
		));
	}
	
	/**
	 * Handles tunnel activation error
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleActiveError(name: string, state: boolean): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? '' : 'de') + 'activateFailed',
				{tunnel: name}
			).toString()
		);
	}

	/**
	 * Changes enabled state of Wireguard tunnel
	 * @param {number} id Wireguard tunnel ID
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private changeEnabledState(id: number, name: string, state: boolean): void {
		this.$store.commit('spinner/SHOW');
		if (state) {
			WireguardService.enableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch(() => this.handleEnableError(name, state));
		} else {
			WireguardService.disableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch(() => this.handleEnableError(name, state));
		}
	}

	/**
	 * Handles tunnel enable success
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleEnableSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? 'enableSuccess' : 'disableSuccess'),
				{tunnel: name}
			).toString()
		));
	}
	
	/**
	 * Handles tunnel enable error
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleEnableError(name: string, state: boolean): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? 'enableFailed' : 'disableFailed'),
				{tunnel: name}
			).toString()
		);
	}

	/**
	 * Removes an existing Wireguard tunnel
	 * @param {number} id Wireguard tunnel id
	 */
	private removeTunnel(id: number, name: string): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.removeTunnel(id)
			.then(() => {
				this.getTunnels().then(() =>this.$toast.success(
					this.$t(
						'network.wireguard.tunnels.messages.removeSuccess',
						{tunnel: name}
					).toString()
				));
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'network.wireguard.tunnels.messages.removeFailed',
						{tunnel: name}
					).toString()
				);
			});
	}
}
</script>
