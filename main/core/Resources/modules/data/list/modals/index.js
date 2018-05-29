import {registry} from '#/main/app/modals/registry'

// TODO : merge the 2 modals definition

import {MODAL_DATA_PICKER, DataPickerModal} from '#/main/core/data/list/modals/containers/data-picker.jsx'
import {MODAL_DATA_LIST, DataListModal} from '#/main/core/data/list/modals/containers/data-list.jsx'

// register list modals
registry.add(MODAL_DATA_PICKER, DataPickerModal)
registry.add(MODAL_DATA_LIST, DataListModal)

// export modal names
export {
  MODAL_DATA_PICKER,
  MODAL_DATA_LIST
}

