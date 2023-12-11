import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Opens the evaluation page of a resource.
 */
export default (resourceNodes, nodesRefresher, path) => ({
  name: 'evaluation',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-award',
  label: trans('open_evaluation', {}, 'actions'),
  target: `${path}/${resourceNodes[0].slug}/evaluation`
})
