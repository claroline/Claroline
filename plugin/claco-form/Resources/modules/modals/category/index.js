import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CategoryFormModal} from '#/plugin/claco-form/modals/category/components/category-form'

const MODAL_CATEGORY_FORM = 'MODAL_CATEGORY_FORM'

// make the modal available for use
registry.add(MODAL_CATEGORY_FORM, CategoryFormModal)

export {
  MODAL_CATEGORY_FORM
}
