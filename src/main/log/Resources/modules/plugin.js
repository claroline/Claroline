/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Log plugin.
 */
registry.add('ClarolineLogBundle', {
    /**
     * Provides Administration tools.
     */
    administration: {
      'logs' : () => { return import(/* webpackChunkName: "main-log-admin-logs" */ '#/main/log/administration/logs') }
    },

    /**
     * Provides current user Account sections.
     */
    account: {
      'logs': () => { return import(/* webpackChunkName: "log-account-functional" */ '#/main/log/account/logs') }
    },
})
