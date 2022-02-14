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
    'scheduled_tasks': () => { return import(/* webpackChunkName: "core-admin-scheduled-task" */ '#/main/scheduler/administration/scheduled-task') }
  }
})
