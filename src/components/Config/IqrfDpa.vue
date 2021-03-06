<template>
	<CCard>
		<CCardHeader>
			{{ $t('config.daemon.interfaces.iqrfDpa.title') }}
		</CCardHeader>
		<CCardBody>
			<CElementCover v-if='loadFailed'>
				{{ $t('config.daemon.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='powerUser'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "config.daemon.interfaces.iqrfDpa.errors.instance"}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required|min:0'
							:custom-messages='{
								integer: "forms.errors.integer",
								min: "config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout",
								required: "config.daemon.interfaces.iqrfDpa.errors.DpaHandlerTimeout"
							}'
						>
							<CInput
								v-model.number='configuration.DpaHandlerTimeout'
								type='number'
								min='0'
								:label='$t("config.daemon.interfaces.iqrfDpa.form.DpaHandlerTimeout")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</fieldset>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {integer, min_value, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfDpa} from '../../interfaces/iqrfInterfaces';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * IQRF DPA component configuration
 */
export default class IqrfDpa extends Vue {
	/**
	 * @constant {string} componentName IQRF DPA component name
	 */
	private componentName = 'iqrf::IqrfDpa'

	/**
	 * @var {IIqrfDpa} configuration IQRF DPA component instance configuration
	 */
	private configuration: IIqrfDpa = {
		component: '',
		instance: '',
		DpaHandlerTimeout: 500,
	}

	/**
	 * @var {string} instance IQRF DPA component instance name
	 */
	private instance = ''

	/**
	 * @var {boolean} powerUser Indicates whether user role is power user
	 */
	private powerUser = false

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

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
	 * Retrieves configuration of IQRF DPA component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'iqrfDpa', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfDpa', success: false});
			});
	}
	
	/**
	 * Saves new or updates existing configuration of IQRF DPA component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.getConfig().then(() => this.$toast.success(this.$t('config.success').toString()));
	}
}
</script>
