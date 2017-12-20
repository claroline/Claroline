import {registerModals} from '#/main/core/layout/modal'

import {MODAL_DATA_PICKER, DataPickerModal} from '#/main/core/data/list/modals/containers/data-picker.jsx'

// register list modals
registerModals([
  [MODAL_DATA_PICKER, DataPickerModal]
])

// export modal names
export {
  MODAL_DATA_PICKER
}
