/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Scheduler plugin.
 */
registry.add('ClarolineSchedulerBundle', {
  /**
   * Provides Administration tools.
   */
  administration: {
    'scheduler': () => { return import(/* webpackChunkName: "core-admin-scheduler" */ '#/main/scheduler/administration/scheduled-task') }
  }
})
