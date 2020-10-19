<template>
	<div>
		<h1>{{ $t('iqrfnet.networkManager.title') }}</h1>
		<CRow>
			<CCol lg='6'>
				<CCard>
					<CTabs variant='tabs' :active-tab='activeTab'>
						<CTab title='IQMESH'>
							<BondingManager @update-devices='updateDevices' />
							<DiscoveryManager @update-devices='updateDevices' />
						</CTab>
						<CTab title='AutoNetwork'>
							<AutoNetwork ref='autonetwork' @update-devices='updateDevices' />
						</CTab>
						<CTab title='Backup/Restore'>
							<Backup />
							<Restore />
						</CTab>
					</CTabs>
				</CCard>
			</CCol>
			<CCol lg='6'>
				<DevicesInfo ref='devs' @notify-autonetwork='getVersion' />
			</CCol>
		</CRow>
	</div>
</template>

<script>
import {CCard, CTab, CTabs} from '@coreui/vue/src';
import Backup from '../../components/IqrfNet/Backup';
import Restore from '../../components/IqrfNet/Restore';
import BondingManager from '../../components/IqrfNet/BondingManager';
import DevicesInfo from '../../components/IqrfNet/DevicesInfo';
import DiscoveryManager from '../../components/IqrfNet/DiscoveryManager';
import AutoNetwork from '../../components/IqrfNet/AutoNetwork';

export default {
	name: 'NetworkManager',
	components: {
		CCard,
		CTab,
		CTabs,
		AutoNetwork,
		Backup,
		BondingManager,
		DevicesInfo,
		DiscoveryManager,
		Restore,
	},
	data() {
		return {
			activeTab: 0,
		};
	},
	methods: {
		updateDevices() {
			this.$refs.devs.getBondedDevices();
		},
		getVersion() {
			this.$refs.autonetwork.getVersion();
		}
	},
	metaInfo: {
		title: 'iqrfnet.networkManager.title',
	},
};
</script>
