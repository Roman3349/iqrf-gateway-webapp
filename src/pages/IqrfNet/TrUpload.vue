<template>
	<div>
		<h1>{{ $t('iqrfnet.trUpload.title') }}</h1>
		<HexUpload />
		<DpaUpdater ref='dpaUpdater' @loaded='osDpaLoaded' />
		<OsUpdater ref='osUpdater' @loaded='osDpaLoaded' @os-upload='osInfoUpload' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {NavigationGuardNext, Route} from 'vue-router';
import {MutationPayload} from 'vuex';
import {WebSocketClientState} from '../../store/modules/webSocketClient.module';
import OsService from '../../services/DaemonApi/OsService';
import DpaUpdater from '../../components/IqrfNet/DpaUpdater.vue';
import HexUpload from '../../components/IqrfNet/HexUpload.vue';
import OsUpdater from '../../components/IqrfNet/OsUpdater.vue';
import {IConfigFetch} from '../../interfaces/daemonComponent';

@Component({
	components: {
		DpaUpdater,
		HexUpload,
		OsUpdater,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('trUpload')) {
				vm.$toast.error(
					vm.$t('iqrfnet.trUpload.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'iqrfnet.trUpload.title'
	}
})

/**
 * Coordinator upload page component
 */
export default class TrUpload extends Vue {
	/**
	 * @constant {number} address IQRF Network address of coordinator
	 */
	private address = 0

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	private loading: Array<string> = [
		'DPA',
		'OS'
	]

	private failed: Array<string> = []

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;}
	
	/**
	 * Vue lifecycle hook created
	 * Initializes validation rules and websocket callbacks
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqrfEmbedOs_Read') {
				this.handleOsInfoResponse(mutation.payload);
			}
		});

		if (this.$store.getters.isSocketConnected) {
			this.getOsInfo();
		} else {
			this.unwatch = this.$store.watch(
				(state: WebSocketClientState, getter: any) => getter.isSocketConnected,
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.getOsInfo();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unwatch();
		this.unsubscribe();
	}

	/**
	 * Sends a Daemon API request to retrieve OS information
	 */
	private getOsInfo(): void {
		this.$store.commit('spinner/SHOW');
		OsService.sendRead(this.address, 60000, 'iqrfnet.trUpload.messages.osInfoFail', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API OS response
	 * @param response Daemon API response
	 */
	private handleOsInfoResponse(response): void {
		if (response.data.status === 0) {
			(this.$refs.dpaUpdater as DpaUpdater).handleOsInfoResponse(response);
			(this.$refs.osUpdater as OsUpdater).handleOsInfoResponse(response.data.rsp.result);
		} else {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('iqrfnet.trUpload.messages.osInfoFail').toString()
			);
			this.$router.push('/iqrfnet');
		}
	}

	/**
	 * Reloads OsInfo after upload
	 */
	private osInfoUpload(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.messages.postUpload').toString()
		);
		this.unwatch = this.$store.watch(
			(state: WebSocketClientState, getter: any) => getter.isSocketConnected,
			(newVal: boolean, oldVal: boolean) => {
				if (!oldVal && newVal) {
					setTimeout(() => this.getOsInfo(), 5000);
					this.unwatch();
				}
			}
		);
	}

	/**
	 * Handles DPA and OS upgrade fetch events
	 */
	private osDpaLoaded(data: IConfigFetch): void {
		if (this.loading.includes(data.name)) {
			this.loading = this.loading.filter((item: string) => item !== data.name);
		}
		if (!data.success) {
			this.failed.push(data.name);
		}
		if (this.loading.length > 0) {
			return;
		}
		this.$store.commit('spinner/HIDE');
		if (this.failed.length === 0) {
			return;
		}
		this.$toast.error(
			this.$t(
				'iqrfnet.trUpload.messages.fetchFailed',
				{children: this.failed.sort().join(', ')},
			).toString()
		);
		this.failed = [];
	}

}
</script>
