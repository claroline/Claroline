/* eslint-disable */

import {registry} from '#/main/app/plugins/registry';

/**
 * Declares applications provided by the Log plugin.
 */
registry.add('ClarolineLogBundle', {
    /**
     * Provides Administration tools.
     */
    administration: {
      'claroline_log_admin_tool' : () => { return import(/* webpackChunkName: "main-log-admin-logs" */ '#/main/log/administration/logs') }
    },

    /**
     * Provides current user Account sections.
     */
    account: {
        'functional': () => { return import(/* webpackChunkName: "log-account-functional" */ '#/main/log/account/functional') },
        'security': () => { return import(/* webpackChunkName: "core-account-security" */ '#/main/log/account/security') },
    },
})
