import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

export default (resourceNodes, nodesRefresher, path) => ({
  name: 'blog_post',
  type: LINK_BUTTON,
  label: trans('new_post', {}, 'icap_blog'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: `${path}/${resourceNodes[0].meta.slug}/new`
})
