import {UserPickerModal} from './components/user-picker'

export const MODAL_USER_PICKER = 'MODAL_USER_PICKER'

const modals = {
  [MODAL_USER_PICKER]: UserPickerModal // todo : register it only in tools using it (users with no edit rights don't need it)
}

function registerModal(type, component) {
  if (!modals[type]) {
    modals[type] = component
  }
}

function registerModals(types) {
  types.map(type => registerModal(type[0], type[1]))
}

export {
  registerModal,
  registerModals
}
