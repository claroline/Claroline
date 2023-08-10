import {FlashcardDeckResource} from '#/plugin/flashcard/resources/flashcard/containers/resource'
import {reducer} from '#/plugin/flashcard/resources/flashcard/store'
import {FlashcardDeckMenu} from '#/plugin/flashcard/resources/flashcard/containers/menu'

/**
 * Flashcard resource application.
 */
export default {
  component: FlashcardDeckResource,
  store: reducer,
  menu: FlashcardDeckMenu,
  styles: ['claroline-distribution-plugin-flashcard-flashcard']
}
