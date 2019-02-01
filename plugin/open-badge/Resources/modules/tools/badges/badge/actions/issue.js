import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api/router'

/* global OpenBadges */

/**
 * Displays a general information about a resource node.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 */
export default (rows) => ({
  type: CALLBACK_BUTTON,
  icon: 'fa fa-fw fa-save',
  label: trans('push-to-backpack'),
  scope: ['object'],
  displayed: true,
  callback: () => {
    OpenBadges.issue([url(['apiv2_open_badge__assertion', {assertion: rows[0].id}, true])], () => {
    })
  }
})
