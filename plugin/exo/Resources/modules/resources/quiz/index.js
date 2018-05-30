import {registerDefaultItemTypes, getDecorators} from '#/plugin/exo/items/item-types'
import {registerDefaultContentItemTypes} from '#/plugin/exo/contents/content-types'
import {registerModals} from '#/main/core/layout/modal'

import {MODAL_ADD_ITEM, AddItemModal} from '#/plugin/exo/quiz/editor/components/modal/add-item-modal.jsx'
import {MODAL_IMPORT_ITEMS, ImportItemsModal} from '#/plugin/exo/quiz/editor/components/modal/import-items-modal.jsx'
import {MODAL_ADD_CONTENT, AddContentModal} from '#/plugin/exo/quiz/editor/components/modal/add-content-modal.jsx'
import {MODAL_CONTENT, ContentModal} from '#/plugin/exo/contents/components/content-modal.jsx'
import {MODAL_MOVE_ITEM, MoveItemModal} from '#/plugin/exo/quiz/editor/components/modal/move-item-modal.jsx'
import {MODAL_DUPLICATE_ITEM, DuplicateItemModal} from '#/plugin/exo/items/components/modal/duplicate-modal.jsx'

import {QuizResource} from '#/plugin/exo/resources/quiz/components/resource'
import {reducer} from '#/plugin/exo/quiz/reducer'
import {normalize} from '#/plugin/exo/quiz/normalizer'
import {decorate} from '#/plugin/exo/quiz/decorators'

registerDefaultItemTypes()
registerDefaultContentItemTypes()

// register modals
registerModals([
  [MODAL_ADD_ITEM, AddItemModal],
  [MODAL_IMPORT_ITEMS, ImportItemsModal],
  [MODAL_ADD_CONTENT, AddContentModal],
  [MODAL_CONTENT, ContentModal],
  [MODAL_MOVE_ITEM, MoveItemModal],
  [MODAL_DUPLICATE_ITEM, DuplicateItemModal]
])

/**
 * Quiz resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: QuizResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-exo-quiz-resource',
  initialData: (initialData) => Object.assign({}, {
    noServer: initialData.noServer,
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  }, decorate(normalize(initialData.quiz), getDecorators(), initialData.resourceNode.permissions.edit))
})
