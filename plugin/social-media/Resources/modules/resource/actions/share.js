import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'share',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-share-alt',
  label: trans('share', {}, 'actions'),
  modal: []
})

export {
  action
}
