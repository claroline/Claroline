import {createElement} from 'react'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {OrganizationCard} from '#/main/community/organization/components/card'

/**
 * Delete organizations action.
 */
export default (organizations, refresher) => {
  const processable = organizations.filter(organization => !get(organization, 'meta.default') && hasPermission('delete', organization))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    disabled: -1 === organizations.findIndex(organization => !get(organization, 'meta.default')),
    displayed: -1 !== organizations.findIndex(organization => hasPermission('delete', organization)),
    dangerous: true,
    confirm: {
      title: transChoice('organization_delete_confirm_title', processable.length, {}, 'community'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('organization_delete_confirm_message', processable.length, {count: processable.length}, 'community'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(organization => createElement(OrganizationCard, {
          key: organization.id,
          orientation: 'row',
          size: 'xs',
          data: organization
        })))
      ]
    },
    request: {
      url: url(['apiv2_organization_delete_bulk'], {ids: processable.map(organization => organization.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
