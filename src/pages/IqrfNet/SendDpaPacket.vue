<template>
	<div>
		<h1>{{ $t('iqrfnet.sendPacket.title') }}</h1>
		<CCard body-wrapper>
			<CElementCover 
				v-if='!isSocketConnected'
				style='z-index: 1;'
			>
				{{ $t('iqrfnet.messages.socketError') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='handleSubmit'>
					<CRow>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='nadr|minLen:2|maxLen:2|required'
								:custom-messages='{
									nadr: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.nadr",
									required: "iqrfnet.sendPacket.form.messages.invalid.nadr"
								}'
							>
								<CInput
									v-model='packetNadr'
									maxlength='4'
									:label='$t("iqrfnet.sendPacket.form.nadr")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									:disabled='addressOverwrite'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pnum|minLen:2|maxLen:2|required'
								:custom-messages='{
									pnum: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.pnum",
									required: "iqrfnet.sendPacket.form.messages.invalid.pnum",
								}'
							>
								<CInput
									v-model='packetPnum'
									:label='$t("iqrfnet.sendPacket.form.pnum")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pcmd|minLen:2|maxLen:2|required'
								:custom-messages='{
									pcmd: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
									required: "iqrfnet.sendPacket.form.messages.invalid.pcmd",
								}'
							>
								<CInput
									v-model='packetPcmd'
									:label='$t("iqrfnet.sendPacket.form.pcmd")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='hwpid|minLen:4|maxLen:4|required'
								:custom-messages='{
									hwpid: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									minLen: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									maxLen: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
									required: "iqrfnet.sendPacket.form.messages.invalid.hwpid",
								}'
							>
								<CInput
									v-model='packetHwpid'
									:label='$t("iqrfnet.sendPacket.form.hwpid")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									style='float: left; width: 25%'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								rules='pdata'
								:custom-messages='{
									pdata: "iqrfnet.sendPacket.form.messages.invalid.pdata"
								}'
							>
								<CInput
									v-model='packetPdata'
									v-maska='{mask: generateMask, tokens: {"H": {pattern: /[0-9a-fA-F]/}}}'
									:label='$t("iqrfnet.sendPacket.form.pdata")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CRow>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='addressOverwrite'
								:label='$t("iqrfnet.sendPacket.form.addressOverwrite")'
							/>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								:disabled='!addressOverwrite'
								:rules='addressOverwrite ? "integer|between:0,239|required" : ""'
								:custom-messages='{
									between: "iqrfnet.sendPacket.form.messages.invalid.address",
									integer: "iqrfnet.sendPacket.form.messages.invalid.address",
									required: "iqrfnet.sendPacket.form.messages.missing.address",
								}'
							>
								<CInput
									v-model.number='address'
									:disabled='!addressOverwrite'
									:label='$t("iqrfnet.sendPacket.form.address")'
									:is-valid='addressOverwrite && touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									min='0'
									max='239'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='timeoutOverwrite'
								:label='$t("iqrfnet.sendPacket.form.timeoutOverwrite")'
							/>
							<ValidationProvider
								v-slot='{valid, touched, errors}'
								:rules='timeoutOverwrite ? "integer|min:0|required" : ""'
								:custom-messages='{
									integer: "iqrfnet.sendPacket.form.messages.invalid.timeout",
									min: "iqrfnet.sendPacket.form.messages.invalid.timeout",
									required: "iqrfnet.sendPacket.form.messages.missing.timeout",
								}'
							>
								<CInput
									v-model.number='timeout'
									:disabled='!timeoutOverwrite'
									:label='$t("iqrfnet.sendPacket.form.timeout")'
									:is-valid='timeoutOverwrite && touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CRow>
						<CCol md='6'>
							<CInputCheckbox
								:checked.sync='autoRepeat'
								:label='$t("iqrfnet.sendPacket.form.autoRepeat")'
								:disabled='invalid'
								@change='handleInterval'
							/> 
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|required|between:10,36000'
								:custom-messages='{
									integer: "forms.errors.integer",
									between: "iqrfnet.sendPacket.form.messages.invalid.autoRepeatInterval",
									required: "iqrfnet.sendPacket.form.messages.invalid.autoRepeatInterval"
								}'
							>
								<CInput
									v-model.number='autoRepeatInterval'
									type='number'
									min='10'
									max='36000'
									:label='$t("iqrfnet.sendPacket.form.autoRepeatInterval")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								>
									<template #append-content>
										<span>
											{{ $t('iqrfnet.sendPacket.form.autoRepeatTime') }}
										</span>
									</template>
								</CInput>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CButton color='primary' type='submit' :disabled='invalid || autoRepeat'>
						{{ $t('forms.send') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
		<DpaMacros @set-packet='setPacket($event)' />
		<div>
			<CRow
				v-for='i of requests.length'
				:key='i'
			>
				<CCol md='6'>
					<JsonMessage
						:message='requests[i - 1]'
						type='request'
						source='sendDpa'
					/>
				</CCol>
				<CCol md='6'>
					<JsonMessage
						v-if='responses.length >= i'
						:message='responses[i-1]'
						type='response'
						source='sendDpa'
					/>
				</CCol>
			</CRow>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CCol, CElementCover, CForm, CInput, CInputCheckbox, CRow} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DpaMacros from '../../components/IqrfNet/DpaMacros.vue';
import JsonMessage from '../../components/IqrfNet/JsonMessage.vue';

import {maska} from 'maska';
import {between, integer, min_value, required, min, max} from 'vee-validate/dist/rules';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';
import sendPacket from '../../iqrfNet/sendPacket';

import {mapGetters, MutationPayload} from 'vuex';
import {RawMessage} from '../../interfaces/dpa';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCol,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		DpaMacros,
		JsonMessage,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			isSocketConnected: 'isSocketConnected',
		}),
	},
	directives: {
		'maska': maska,
	},
	metaInfo: {
		title: 'iqrfnet.sendPacket.title',
	}
})

/**
 * Send Raw DPA packet page component
 */
export default class SendDpaPacket extends Vue {
	/**
	 * @var {number} address Default device address
	 */
	private address = 0

	/**
	 * @var {boolean} addressOverwrite Controls whether packet address bytes should be overwritten
	 */
	private addressOverwrite = false

	/**
	 * @var {boolean} autoRepeat Send request repeatedly in specified interval
	 */
	private autoRepeat = false

	/**
	 * @var {number} autoRepeatInterval Interval in ms * 100
	 */
	private autoRepeatInterval = 10

	/**
	 * @var {number} intervalId Interval ID
	 */
	private intervalId = 0

	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = ''

	/**
	 * @var {string} packetNadr Packet NADR bytes
	 */
	private packetNadr = '00'

	/**
	 * @var {string} packetPnum Packet PNUM byte
	 */
	private packetPnum = '00'

	/**
	 * @var {string} packetPcmd Packet PCMD byte
 	 */
	private packetPcmd = '00'

	/**
	 * @var {string} packetHwpid Packet HWPID bytes
	 */
	private packetHwpid = 'ffff'

	/**
	 * @var {string} packetPdata Packet PDATA bytes
	 */
	private packetPdata = ''

	/**
	 * @var {Array<string>} request Daemon api request message, used in message card
	 */
	private requests: Array<string> = []

	/**
	 * @var {Array<string>} response Daemon api response message, used in message card
	 */
	private responses: Array<string> = []

	/**
	 * @var {number} timeout Default daemon api message timeout
	 */
	private timeout = 1000

	/**
	 * @var {boolean} timeoutOverwrite Controls whether default daemon api message timeout should be overwritten
	 */
	private timeoutOverwrite = false

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Computes packet string from packet parts
	 * @returns {string} Packet string
	 */
	get packet(): string {
		let packet = this.packetNadr + '.00.';
		packet += this.packetPnum + '.' + this.packetPcmd + '.';
		packet += this.packetHwpid.substr(0, 2) + '.' + this.packetHwpid.substr(2, 2) + '.' + this.packetPdata;
		return packet;
	}

	/**
	 * Generates packet pdata pmask
	 * @returns {string} packet pdata mask
	 */
	get generateMask(): string {
		return 'HH.'.repeat(56) + 'HH';
	}

	@Watch('isSocketConnected')
	private errorRecovery(): void {
		if (!this.$store.getters.isSocketConnected) {
			if (this.autoRepeat) {
				this.autoRepeat = false;
				clearTimeout(this.intervalId);
			} else {
				this.$store.commit('spinner/HIDE');
			}
			this.$store.dispatch('removeMessage', this.msgId);
			this.requests.shift();
		}
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('minLen', min);
		extend('maxLen', max);
		extend('required', required);
		extend('nadr', (nadr: string) => {
			const re = new RegExp('^[0-9a-f]{2}$', 'i');
			return re.test(nadr);
		});
		extend('pnum', (pnum: string) => {
			const re = new RegExp('^[0-9a-f]{2}$', 'i');
			return re.test(pnum);
		});
		extend('pcmd', (pcmd: string) => {
			const re = new RegExp('^[0-7][0-9a-f]$', 'i');
			return re.test(pcmd);
		});
		extend('hwpid', (hwpid: string) => {
			const re = new RegExp('^[0-9a-f]{4}$', 'i');
			return re.test(hwpid);
		});
		extend('pdata', (pdata: string) => {
			const re = new RegExp('^([0-9a-f]{2}\\.){0,56}[0-9a-f]{2}(\\.|)$', 'i');
			return re.test(pdata);
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONSEND' && mutation.payload.mType === 'iqrfRaw') {
				if (this.autoRepeat) {
					this.requests.unshift(JSON.stringify(mutation.payload, null, 4));
				} else {
					this.requests = [JSON.stringify(mutation.payload, null, 4)];
					this.responses = [];
				}
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			this.$store.commit('spinner/HIDE');
			if (mutation.payload.mType === 'messageError') {
				this.handleMessageError(mutation.payload);
				return;
			}
			if (mutation.payload.data.msgId === this.msgId && mutation.payload.mType === 'iqrfRaw') {
				this.$store.dispatch('removeMessage', this.msgId);
				this.handleResponse(mutation.payload);
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		clearTimeout(this.intervalId);
		this.unsubscribe();
	}

	/**
	 * Sets interval and asynchronous DPA packet sending if auto repeat checkbox is set to true.
	 * Clears interval otherwise.
	 */
	private handleInterval(): void {
		if (this.autoRepeat) {
			this.requests = [];
			this.responses = [];
			this.intervalId = window.setInterval(this.handleSubmit, this.autoRepeatInterval*100);
			return;
		}
		clearInterval(this.intervalId);
	}

	/**
	 * Handles Send DPA packet form submit event
	 */
	private handleSubmit(): Promise<string>|void {
		if (this.packet === '') {
			this.$toast.error(this.$t('iqrfnet.sendPacket.form.messages.missing.packet').toString());
			return;
		}
		const json: RawMessage = {
			'mType': 'iqrfRaw',
			'data': {
				'req': {
					'rData': this.packet,
				},
				'returnVerbose': true,
			},
		};
		if (this.addressOverwrite) {
			json.data.req.rData = sendPacket.updateNadr(this.packet, this.address);
		}
		if (this.timeoutOverwrite) {
			json.data.timeout = this.timeout;
		}
		let options = new WebSocketOptions(json);
		const packet = sendPacket.Packet.parse(this.packet);
		if (packet.nadr === 255) {
			options.timeout = 1000;
		} else if (packet.pnum === 2 && (packet.pcmd === 5 || packet.pcmd === 11)) {
			options.timeout = 1000;
		} else {
			options.timeout = 60000;
			options.message = 'iqrfnet.sendPacket.messages.failure';
			if (!this.autoRepeat) {
				this.$store.commit('spinner/SHOW');
			}
		}
		options.callback = () => this.msgId = '';
		return this.$store.dispatch('sendRequest', options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API messageError response
	 */
	private handleMessageError(response): void {
		if (this.autoRepeat) {
			this.responses.unshift(JSON.stringify(response, null, 4));
			this.autoRepeat = false;
			clearTimeout(this.intervalId);
		} else {
			this.responses = [JSON.stringify(response, null, 4)];
		}
		this.$store.dispatch('removeMessage', this.msgId);
		this.$toast.clear();
		this.$toast.error(
			this.$t('iqrfnet.sendPacket.messages.queueFull').toString()
		);
	}

	/**
	 * Handles Daemon API Raw message responses
	 * @param response Daemon API response payload
	 */
	private handleResponse(response): void {
		if (this.autoRepeat) {
			this.responses.unshift(JSON.stringify(response, null, 4));
		} else {
			this.responses = [JSON.stringify(response, null, 4)];
		}
		let message = '';
		let error = true;
		switch (response.data.status) {
			case 0:
				message = this.$t('iqrfnet.sendPacket.messages.success').toString();
				error = false;
				break;
			case 2:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.pcmd').toString();
				break;
			case 3:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.pnum').toString();
				break;
			case 5:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.dataLength').toString();
				break;
			case 6:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.data').toString();
				break;
			case 7:
				message = this.$t('iqrfnet.sendPacket.messages.incorrect.hwpid').toString();
				break;
			case 8:
				message = this.$t('forms.messages.noDevice',
					{
						address: (this.addressOverwrite ? this.address : Number.parseInt(this.packetNadr, 16))
					}).toString();
				break;
			default:
				message = this.$t('iqrfnet.sendPacket.messages.failure').toString();
				break;
		}
		this.$toast.clear();
		this.$toast.open({
			message: message,
			type: (error ? 'error': 'success'),
			position: 'top',
			dismissible: true,
			duration: 5000,
			pauseOnHover: true
		});
	}

	/**
	 * Removes dots from string packet representation
	 * @param {string} bytes Packet bytes
	 * @returns {string} Packet byte string without dot separators
	 */
	private joinBytes(bytes: string): string {
		return bytes.replace(/\./g, '');
	}

	/**
	 * Sets new DPA packet
	 * @param {string} newPacket New DPA packet
	 */
	private setPacket(newPacket: string): void {
		this.packetNadr = this.joinBytes(newPacket.substr(0, 3));
		this.packetPnum = this.joinBytes(newPacket.substr(6, 3));
		this.packetPcmd = this.joinBytes(newPacket.substr(9, 3));
		this.packetHwpid = this.joinBytes(newPacket.substr(12, 5));
		if (newPacket.length > 17) {
			this.packetPdata = newPacket.substr(18, newPacket.length - 1);
		} else {
			this.packetPdata = '';
		}
		this.setTimeout();
	}

	/**
	 * Sets DPA timeout
	 */
	private setTimeout(): void {
		let packet = sendPacket.Packet.parse(this.packet);
		let newTimeout = packet.detectTimeout();
		if (newTimeout === null) {
			this.timeoutOverwrite = false;
			this.timeout = 1000;
		} else {
			this.timeoutOverwrite = true;
			this.timeout = newTimeout;
		}
	}
}
</script>
