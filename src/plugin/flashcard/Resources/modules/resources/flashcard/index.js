import {reducer} from '#/plugin/flashcard/resources/flashcard/store'
import {FlashcardResource} from '#/plugin/flashcard/resources/flashcard/containers/resource'

/**
 * Flashcard resource application.
 */
export default {
  component: FlashcardResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-flashcard-flashcard']
}
