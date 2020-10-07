import store from '../store';
import {WebSocketOptions} from '../store/modules/webSocketClient.module';

export enum DaemonMode {
	getMode = '',
	forwarding = 'forwarding',
	operational = 'operational',
	service = 'service',
	unknown = 'unknown',
}

/**
 * IQRF Gateway Daemon operational mode service
 */
class DaemonModeService {

	/**
	 * Retrieve the current operational mode
	 */
	get(timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		return this.set(DaemonMode.getMode, timeout, message, callback);
	}

	/**
	 * Sets a new mode operational mode
	 * @param newMode New operational mode
	 */
	set(newMode: DaemonMode, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		const request = {
			'mType': 'mngDaemon_Mode',
			'data': {
				'req': {
					'operMode': newMode,
				},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Parses Daemon mode response
	 * @param response Response from IQRF Gateway Daemon
	 */
	parse(response: any): DaemonMode {
		try {
			return response.data.rsp.operMode as DaemonMode;
		} catch (e) {
			return DaemonMode.unknown;
		}
	}
}

export default new DaemonModeService();
