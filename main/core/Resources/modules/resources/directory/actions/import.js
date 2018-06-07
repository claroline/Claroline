import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'import',
  type: 'modal',
  icon: 'fa fa-fw fa-upload',
  label: trans('import', {}, 'actions'),
  modal: []
})

export {
  action
}
