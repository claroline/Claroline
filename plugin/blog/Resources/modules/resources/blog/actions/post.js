import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'post',
  type: LINK_BUTTON,
  label: trans('new_post', {}, 'icap_blog'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/new'
})

export {
  action
}
