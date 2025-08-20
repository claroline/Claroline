import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants, declareAction} from '#/main/app/action'
import {hasPermission} from '#/main/app/security'

export default declareAction((resourceNodes, nodesRefresher, path) => ({
  name: 'add-entry',
  type: LINK_BUTTON,
  label: trans('add-entry', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: `${path}/${resourceNodes[0].slug}/entry/form`,
  exact: true,
  displayed: hasPermission('contribute', resourceNodes[0]),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_DETAILS]
}))
