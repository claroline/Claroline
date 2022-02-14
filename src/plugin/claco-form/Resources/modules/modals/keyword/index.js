import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {KeywordModal} from '#/plugin/claco-form/modals/keyword/containers/modal'

const MODAL_KEYWORD_FORM = 'MODAL_KEYWORD_FORM'

// make the modal available for use
registry.add(MODAL_KEYWORD_FORM, KeywordModal)

export {
  MODAL_KEYWORD_FORM
}
