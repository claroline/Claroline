/* eslint-disable */

import {registry} from '#/main/app/plugins/registry';

/**
 * Declares applications provided by the Log plugin.
 */
registry.add('ClarolineLogBundle', {
    administration: {
      'claroline_log_admin_tool' : () => { return import(/* webpackChunkName: "plugin-log-admin-logs" */ '#/main/log/administration/logs') }
    }
})
