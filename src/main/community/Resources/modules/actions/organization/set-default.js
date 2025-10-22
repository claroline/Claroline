import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'

/**
 * Set the default organization action.
 */
export default declareAction((organizations, refresher) => {
  const processable = organizations.filter(organization => !get(organization, 'meta.default') && hasPermission('administrate', organization))

  return {
    name: 'set-default',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-arrow-right-arrow-left',
    label: trans('set_default', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: trans('organization_set_default_confirm', {}, 'community'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: ['apiv2_organization_set_default'],
      request: {
        method: 'PUT',
        body: JSON.stringify(0 !== processable.length ? processable[0].id : null)
      },
      success: () => refresher.update(processable)
    },
    group: trans('management'),
    scope: ['object']
  }
})
