import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'post',
  type: 'link',
  label: trans('create_subject', {}, 'forum'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  target: '/subjects/form'
})

export {
  action
}
