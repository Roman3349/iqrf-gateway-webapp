<template>
	<div>
		<h1>{{ $t('network.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem to='/network/ethernet'>
					<header class='list-group-item-heading'>
						{{ $t('network.ethernet.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('network.ethernet.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/network/wireless'>
					<header class='list-group-item-heading'>
						{{ $t('network.wireless.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('network.wireless.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/network/vpn'>
					<header class='list-group-item-heading'>
						{{ $t('network.wireguard.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('network.wireguard.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'network.title',
	},
})

/**
 * Network disambiguation menu component
 */
export default class NetworkDisambiguation extends Vue {
}
</script>
