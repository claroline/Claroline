import {reducer} from '#/plugin/flashcard/resources/flashcard/store'
import {FlashcardResource} from '#/plugin/flashcard/resources/flashcard/containers/resource'
import {FlashcardMenu} from '#/plugin/flashcard/resources/flashcard/containers/menu'

/**
 * Flashcard resource application.
 */
export default {
  component: FlashcardResource,
  menu: FlashcardMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-flashcard-flashcard']
}
