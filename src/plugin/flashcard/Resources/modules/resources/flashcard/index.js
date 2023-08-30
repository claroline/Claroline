import {FlashcardResource} from '#/plugin/flashcard/resources/flashcard/containers/resource'
import {reducer} from '#/plugin/flashcard/resources/flashcard/store'
import {FlashcardMenu} from '#/plugin/flashcard/resources/flashcard/components/menu'

/**
 * Flashcard resource application.
 */
export default {
  component: FlashcardResource,
  store: reducer,
  menu: FlashcardMenu,
  styles: ['claroline-distribution-plugin-flashcard-flashcard']
}
