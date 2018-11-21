import {trans} from '#/main/app/intl/translation'

export default () => ({
  name: 'shortcuts',
  type: 'modal',
  icon: 'fa fa-fw fa-share',
  label: trans('show-shortcuts', {}, 'actions'),
  modal: []
})
