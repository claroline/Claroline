import {registerModals} from '#/main/core/layout/modal'

import {MODAL_DATA_PICKER, DataPickerModal} from '#/main/core/data/list/modals/containers/data-picker.jsx'
import {MODAL_DATA_LIST, DataListModal} from '#/main/core/data/list/modals/containers/data-list.jsx'

// register list modals
registerModals([
  [MODAL_DATA_PICKER, DataPickerModal],
  [MODAL_DATA_LIST, DataListModal]
])

// export modal names
export {
  MODAL_DATA_PICKER,
  MODAL_DATA_LIST
}
