
import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {ItemFormModal} from '#/plugin/exo/items/modals/form/components/modal'

const MODAL_ITEM_FORM = 'MODAL_ITEM_FORM'

// make the modal available for use
registry.add(MODAL_ITEM_FORM, ItemFormModal)

export {
  MODAL_ITEM_FORM
}
