<template>
	<CHeader :fixed='false' color-scheme='dark'>
		<CToggler
			in-header
			class='ml-3 d-lg-none'
			@click='$store.commit("sidebar/toggleSidebarMobile")'
		/>
		<CToggler
			in-header
			class='ml-3 d-md-down-none'
			@click='$store.commit("sidebar/toggleSidebarDesktop")'
		/>
		<CHeaderBrand class='ml-auto d-lg-none' to='/'>
			<LogoBig :alt='$t("core.title")' />
		</CHeaderBrand>
		<CHeaderNav class='ml-auto mr-3'>
			<CDropdown
				v-if='$store.getters["user/isLoggedIn"]'
				:in-nav='true'
				class='c-header-nav-items'
				placement='bottom-end'
				add-menu-classes='pt-0'
			>
				<template #toggler>
					<CHeaderNavLink class='dropdown-toggle'>
						{{ $store.getters['user/getName'] }}
					</CHeaderNavLink>
				</template>
				<CDropdownItem @click='signOut'>
					<CIcon :content='icons.logout' />
					{{ $t('core.sign.out.title') }}
				</CDropdownItem>
			</CDropdown>
		</CHeaderNav>
	</CHeader>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CBadge,
	CDropdown,
	CHeader,
	CHeaderBrand,
	CHeaderNav,
	CHeaderNavLink,
	CIcon,
	CToggler,
} from '@coreui/vue/src';
import LogoBig from '../assets/logo-big.svg';
import {cilLockLocked} from '@coreui/icons';
import { Dictionary } from 'vue-router/types/router';

@Component({
	components: {
		CBadge,
		CDropdown,
		CHeader,
		CHeaderBrand,
		CHeaderNav,
		CHeaderNavLink,
		CIcon,
		CToggler,
		LogoBig,
	}
})

/**
 * Header component
 */
export default class TheHeader extends Vue {
	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		logout: cilLockLocked
	}
	
	/**
	 * User signout method, redirects to the signin page
	 */
	private signOut(): void {
		this.$store.dispatch('user/signOut')
			.then(() => {
				this.$router.push('/sign/in');
				this.$toast.success(this.$t('core.sign.out.message').toString());
			});
	}
}
</script>
