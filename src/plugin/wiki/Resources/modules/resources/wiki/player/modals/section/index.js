import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {SectionDeleteModal} from '#/plugin/wiki/resources/wiki/player/modals/section/components/delete'

const MODAL_WIKI_SECTION_DELETE = 'MODAL_WIKI_SECTION_DELETE'

// make the modal available for use
registry.add(MODAL_WIKI_SECTION_DELETE, SectionDeleteModal)

export {
  MODAL_WIKI_SECTION_DELETE
}