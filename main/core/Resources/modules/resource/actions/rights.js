import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {getSimpleAccessRule, hasCustomRules} from '#/main/core/resource/rights'
import {MODAL_RESOURCE_RIGHTS} from '#/main/core/resource/modals/rights'

/**
 * Displays a form to configure the rights of some resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => { // todo collection
  // computes simplified version of node rights
  let icon = 'fa-lock'
  let customRules = false
  if (1 === resourceNodes.length) {
    const rights = getSimpleAccessRule(resourceNodes[0].rights, resourceNodes[0].workspace)
    customRules = hasCustomRules(resourceNodes[0].rights, resourceNodes[0].workspace)

    switch (rights) {
      case 'all':
        icon = 'fa-unlock'
        break
      case 'user':
        icon = 'fa-unlock'
        break
      case 'workspace':
        icon = 'fa-unlock-alt'
        break
      case 'admin':
        icon = 'fa-lock'
        break
    }
  }

  return {
    name: 'rights',
    type: MODAL_BUTTON,
    icon: classes('fa fa-fw', icon),
    label: trans('edit-rights', {}, 'actions'),
    modal: [MODAL_RESOURCE_RIGHTS, {
      resourceNode: 1 === resourceNodes.length && resourceNodes[0],
      updateNode: (resourceNode) => nodesRefresher.update([resourceNode])
    }],
    subscript: customRules ? {
      type: 'text',
      status: 'danger',
      value: (<span className="fa fa-asterisk" />)
    } : undefined
  }
}
