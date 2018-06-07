import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'share',
  type: 'modal',
  icon: 'fa fa-fw fa-share-alt',
  label: trans('share', {}, 'actions'),
  modal: []
})

export {
  action
}
