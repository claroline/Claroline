import {FlashcardDeckResource} from '#/plugin/flashcard/resources/flashcard/containers/resource'
import {reducer} from '#/plugin/flashcard/resources/flashcard/store'

/**
 * Flashcard resource application.
 */
export default {
  component: FlashcardDeckResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-flashcard-flashcard']
}
