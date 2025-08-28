import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {constants, declareAction} from '#/main/app/action'
import {hasPermission} from '#/main/app/security'

export default declareAction((resourceNodes) => ({
  name: 'assign-categories',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-refresh',
  label: trans('recalculate', {}, 'actions'),
  title: trans('assign_categories', {}, 'actions'),
  description: trans('assign_categories_desc', {}, 'actions'),
  request: {
    url: ['apiv2_clacoform_category_assign_all', {id: resourceNodes[0].id}],
    request: {method: 'PUT'}
  },
  group: trans('management'),
  displayed: hasPermission('follow', resourceNodes[0]),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD]
}))
