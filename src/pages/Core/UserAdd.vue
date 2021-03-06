<template>
	<div>
		<h1>{{ $t('core.user.add') }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "forms.errors.username",
						}'
					>
						<CInput
							v-model='username'
							:label='$t("forms.fields.username")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "forms.errors.password",
						}'
					>
						<CInput
							v-model='password'
							:label='$t("forms.fields.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							type='password'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='$store.getters["user/getRole"] === "power"'
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "core.user.errors.role",
						}'
					>
						<CSelect
							:value.sync='role'
							:label='$t("core.user.role")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.role")'
							:options='[
								{value: "normal", label: $t("core.user.roles.normal")},
								{value: "power", label: $t("core.user.roles.power")},
							]'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-if='$store.getters["user/getRole"] === "power"'
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "core.user.errors.language",
						}'
					>
						<CSelect
							:value.sync='language'
							:label='$t("core.user.language")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("core.user.errors.language")'
							:options='[
								{value: "en", label: $t("core.user.languages.en")},
							]'
						/>
					</ValidationProvider>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.add') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import UserService from '../../services/UserService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'core.user.add',
	}
})

/**
 * User manager form to add a new user
 */
export default class UserAdd extends Vue {
	/**
	 * @var {string} language User's preferred language
	 */
	private language = ''

	/**
	 * @var {string} password User password
	 */
	private password = ''

	/**
	 * @var {string} role User role
	 */
	private role = ''

	/**
	 * @var {string} username User name
	 */
	private username = ''

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Creates a new user entry with default language and role if unspecified
	 */
	private handleSubmit(): void {
		const language = this.language === '' ? 'en' : this.language;
		const role = this.role === '' ? 'normal' : this.role;
		this.$store.commit('spinner/SHOW');
		UserService.add(this.username, this.password, language, role)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.addSuccess',
						{username: this.username}
					).toString()
				);
				this.$router.push('/user/');
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'core.user.messages.addFailed',
						{error: error.response ? error.response.data.message : error.message},
					).toString()
				);
			});
	}
}
</script>
