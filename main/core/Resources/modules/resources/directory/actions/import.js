import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'import',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-upload',
  label: trans('import', {}, 'actions'),
  modal: []
})

export {
  action
}
