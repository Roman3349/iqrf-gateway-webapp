<template>
	<div>
		<h1>{{ $t('config.main.title') }}</h1>
		<CCard>
			<CCardBody>
				<CForm @submit.prevent='saveConfig'>
					<CInput
						v-model='configuration.applicationName'
						:label='$t("config.main.form.applicationName")'
					/>
					<CInput
						v-model='configuration.resourceDir'
						:label='$t("config.main.form.resourceDir")'
					/>
					<CInput
						v-model='configuration.dataDir'
						:label='$t("config.main.form.dataDir")'
					/>
					<CInput
						v-model='configuration.cacheDir'
						:label='$t("config.main.form.cacheDir")'
					/>
					<CInput
						v-model='configuration.userDir'
						:label='$t("config.main.form.userDir")'
					/>
					<CInput
						v-model='configuration.configurationDir'
						:label='$t("config.main.form.configurationDir")'
					/>
					<CInput
						v-model='configuration.deploymentDir'
						:label='$t("config.main.form.deploymentDir")'
					/>
					<CButton type='submit' color='primary'>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default Vue.extend({
	name: 'MainConfiguration',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
	},
	data(): any {
		return {
			configuration: {
				applicationName: null,
				resourceDir: null,
				dataDir: null,
				cacheDir: null,
				userDir: null,
				configurationDir: null,
				deploymentDir: null,
			},
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.getComponent('')
				.then((response: AxiosResponse) =>  {
					this.$store.commit('spinner/HIDE');
					this.configuration = response.data;
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		},
		saveConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.updateComponent('', this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		},
		successfulSave() {
			this.$store.commit('spinner/HIDE');
			this.$toast.success(this.$t('config.success').toString());
		},
	},
	metaInfo: {
		title: 'config.main.title',
	},
});
</script>