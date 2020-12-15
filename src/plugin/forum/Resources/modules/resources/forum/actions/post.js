import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

export default (resourceNodes, nodesRefresher, path) => ({
  name: 'post',
  type: LINK_BUTTON,
  label: trans('create_subject', {}, 'forum'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: `${path}/${resourceNodes[0].slug}/subjects/form`
})
