import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'create-announce',
  type: 'link',
  label: trans('add_announce', {}, 'announcement'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/add'
})

export {
  action
}
