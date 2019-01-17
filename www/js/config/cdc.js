/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

'use strict';

// Select IQRF CDC interface port from list
let cdcPorts = document.getElementsByClassName('btn-cdc-port');
for (let i = 0; i < cdcPorts.length; i++) {
	cdcPorts[i].addEventListener('click', function (event) {
		document.getElementById('frm-configIqrfCdcForm-IqrfInterface').value = event.currentTarget.dataset.port;
	});
}
