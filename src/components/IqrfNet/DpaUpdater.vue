<template>
	<div>
		<CCard>
			<CCardHeader>{{ $t('iqrfnet.trUpload.dpaUpload.title') }}</CCardHeader>
			<CCardBody>
				<CElementCover 
					v-if='loadFailed'
					style='z-index: 1;'
				>
					{{ $t('iqrfnet.trUpload.dpaUpload.messages.dpaFetchFailure') }}
				</CElementCover>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='compareUploadedVersion'>
						<fieldset :disabled='loadFailed'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='required'
								:custom-messages='{
									required: "iqrfnet.trUpload.dpaUpload.errors.version",
								}'
							>
								<CSelect
									:value.sync='version'
									:label='$t("iqrfnet.trUpload.dpaUpload.form.version")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									:placeholder='$t("iqrfnet.trUpload.dpaUpload.errors.version")'
									:options='versions'
								/>
							</ValidationProvider>
							<CButton type='submit' color='primary' :disabled='invalid'>
								{{ $t('forms.upload') }}
							</CButton>
						</fieldset>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
		<CModal
			color='warning'
			:show.sync='showModal'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('iqrfnet.trUpload.dpaUpload.messages.modalTitle') }}
				</h5>
			</template>
			{{ $t('iqrfnet.trUpload.dpaUpload.messages.modalPrompt', {version: prettyVersion(currentDpa)}) }}
			<template #footer>
				<CButton
					color='secondary'
					@click='showModal = false'
				>
					{{ $t('forms.no') }}
				</CButton> <CButton
					color='warning'
					@click='{{showModal = false; getDpaFile()}}'
				>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CAlert, CButton, CCard, CCardBody, CCardHeader, CForm, CModal, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import DpaService, {RFMode} from '../../services/IqrfRepository/DpaService';
import IqrfNetService from '../../services/IqrfNetService';
import {MutationPayload} from 'vuex';
import {AxiosError, AxiosResponse} from 'axios';
import IqrfService from '../../services/IqrfService';
import ServiceService from '../../services/ServiceService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

interface DpaVersions {
	label: string
	value: string
}

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CModal,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Dpa updater card for TrUpload component
 */
export default class DpaUpdater extends Vue {
	/**
	 * @constant {number} address Network address of device
	 */
	private address = 0

	/**
	 * @var {string|null} currentDpa Current version of DPA
	 */
	private currentDpa: string|null = null

	/**
	 * @var {string|null} interfaceType Active IQRF communication interface
	 */
	private interfaceType: string|null = null

	/**
	 * @var {boolean} loadFailed Indicates whether DPA upgrades fetch failed
	 */
	private loadFailed = false

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string|null} osBuild IQRF OS build
	 */
	private osBuild: string|null = null

	/**
	 * @var {number|null} trType Transciever type identifier
	 */
	private trType: number|null = null

	/**
	 * @var {boolean} showModal Controls whether DPA upload modal is shown
	 */
	private showModal = false

	/**
	 * @var {string|null} version Currently selected version of DPA
	 */
	private version: string|null = null

	/**
	 * @var {Array<DpaVersions>} versions Array of available DPA versions to update to
	 */
	private versions: Array<DpaVersions> = []

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				if (mutation.payload.data.status === 0) {
					this.interfaceType = mutation.payload.data.rsp.osRead.flags.interfaceType;
					this.$emit('loaded', {name: 'DPA', success: true});
				} else {
					this.$emit('loaded', {name: 'DPA', success: false});
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Converts DPA version from integer representation to string
	 * @param {number} version DPA version
	 * @returns {string} String representation of DPA version
	 */
	convertVersion(version: number): string {
		return version.toString(16).padStart(4, '0').toUpperCase();
	}

	/**
	 * Converts DPA version string to pretty version
	 * @param {string} version DPA version string
	 * @returns {string} DPA version pretty string
	 */
	prettyVersion(version: string): string {
		if (version === null) {
			return 'unknown';
		}
		if (version.startsWith('0')) {
			return version.charAt(1) + '.' + version.substr(2, 2);
		}
		return version.substr(0, 2) + '.' + version.substr(2, 2);
	}

	/**
	 * Performs device enumeration
	 */
	private getDeviceEnumeration(): void {
		IqrfNetService.enumerateDevice(this.address, 60000, 'iqrfnet.enumeration.messages.failure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * EmbedOs info response handler
	 */
	public handleOsInfoResponse(response: any): void {
		const result = response.data.rsp.result;
		this.osBuild = this.convertVersion(result.osBuild);
		this.trType = result.trMcuType;
		this.currentDpa = this.convertVersion(result.dpaVer);
		DpaService.getVersions(this.osBuild)
			.then((versions) => {
				let fetchedVersions: Array<DpaVersions> = [];
				for (const version of versions) {
					const dpaVer = Number.parseInt(version.getVersion(false));
					if (dpaVer < 400) {
						fetchedVersions.push({
							value: version.getVersion(false) + '-' + RFMode.LP,
							label: version.getVersion(true) + ', ' + RFMode.LP + ' RF mode'
						});
						fetchedVersions.push({
							value: version.getVersion(false) + '-' + RFMode.STD,
							label: version.getVersion(true) + ', ' + RFMode.STD + ' RF mode'
						});
					} else {
						fetchedVersions.push({
							value: version.getVersion(false),
							label: version.getVersion(true),
						});
					}
				}
				fetchedVersions.forEach(item => {
					if (this.currentDpa === item.value) {
						Object.assign(item, {label: item.label + ' (Current version)'});
					}
				});
				this.versions = fetchedVersions.sort().reverse();
				this.getDeviceEnumeration();
			})
			.catch(() => {
				this.$emit('loaded', {name: 'DPA', success: false});
			});
	}

	/**
	 * Updates list of DPA version to reflect changes made by upload
	 */
	private updateVersions(): void {
		for (let item of this.versions) {
			if (item.label.endsWith('(Current version)')) {
				item.label = item.label.slice(0, -18);
			}
			if (item.value === this.version) {
				item.label += ' (Current version)';
				continue;
			}
		}
		this.currentDpa = this.version;
	}

	/**
	 * Displays a modal window if new version is the same as current version, otherwise executes upload
	 */
	private compareUploadedVersion(): void {
		if (this.version === null) {
			return;
		}
		if (this.currentDpa === this.version) {
			this.showModal = true;
		} else {
			this.getDpaFile();
		}
	}

	/**
	 * Retrieves DPA file to upload
	 */
	private getDpaFile(): void {
		if (this.version === null) {
			return;
		}

		// build dpa file fetch metadata
		const request = {
			'interfaceType': this.interfaceType,
			'osBuild': this.osBuild,
			'trSeries': this.trType,
		};
		if (this.version.endsWith('-STD')) {
			Object.assign(request, {'dpa': this.version.split('-')[0]});
			Object.assign(request, {'rfMode': RFMode.STD});
		} else if (this.version.endsWith('-LP')) {
			Object.assign(request, {'dpa': this.version.split('-')[0]});
			Object.assign(request, {'rfMode': RFMode.LP});
		} else {
			Object.assign(request, {'dpa': this.version});
		}

		// fetch dpa file
		this.$store.commit('spinner/SHOW');
		DpaService.getDpaFile(request)
			.then((response: AxiosResponse) => {
				// update spinner message
				this.$store.commit(
					'spinner/UPDATE_TEXT', 
					this.$t(
						'iqrfnet.trUpload.osUpload.messages.fileUploading',
						{file: 'DPA'}
					).toString()
				);
				this.stopDaemon(response.data.fileName);
				
			})
			.catch((error: AxiosError) => FormErrorHandler.fileFetchError(error));
	}

	/**
	 * Attempts to fetch a DPA plugin file and store it.
	 * If the fetch is successful, the name of the file is returned in response and a NativeUpload daemon api call is executed.
	 */
	private upload(fileName: string): void {
		IqrfService.uploader({name: fileName, type: 'DPA'})
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT',  
					this.$t(
						'iqrfnet.trUpload.osUpload.messages.fileUploaded',
						{file: 'DPA'}
					).toString()
				);
				this.startDaemon();
			})
			.catch((error: AxiosError) => FormErrorHandler.uploadUtilError(error));
	}

	/**
	 * Stops the IQRF Daemon service before upgrading OS
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private stopDaemon(fileName: string): Promise<void> {
		return ServiceService.stop('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/UPDATE_TEXT', 
					this.$t(
						'service.iqrf-gateway-daemon.messages.stop').toString()
				);
				this.upload(fileName);
			})
			.catch((error: AxiosError) => FormErrorHandler.serviceError(error));
	}

	/**
	 * Starts the IQRF Daemon service upon successful OS upgrade
	 */
	private startDaemon(): void {
		ServiceService.start('iqrf-gateway-daemon')
			.then(() => {
				this.updateVersions();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('iqrfnet.trUpload.dpaUpload.messages.uploadSuccess').toString()
				);
			})
			.catch((error: AxiosError) => FormErrorHandler.serviceError(error));
	}
}
</script>

