import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'post',
  type: 'link',
  label: trans('new_post', {}, 'icap_blog'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/new'
})

export {
  action
}
