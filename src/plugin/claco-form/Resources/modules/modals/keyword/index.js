import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {KeywordFormModal} from '#/plugin/claco-form/modals/keyword/components/keyword-form'

const MODAL_KEYWORD_FORM = 'MODAL_KEYWORD_FORM'

// make the modal available for use
registry.add(MODAL_KEYWORD_FORM, KeywordFormModal)

export {
  MODAL_KEYWORD_FORM
}
