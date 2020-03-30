/**
 * Icon item form modal.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {IconItemFormModal} from '#/main/theme/administration/appearance/icon/modals/icon-item/containers/modal'

const MODAL_ICON_ITEM_FORM = 'MODAL_ICON_ITEM_FORM'

// make the modal available for use
registry.add(MODAL_ICON_ITEM_FORM, IconItemFormModal)

export {
  MODAL_ICON_ITEM_FORM
}
