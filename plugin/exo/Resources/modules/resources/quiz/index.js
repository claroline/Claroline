import {registry} from '#/main/app/modals/registry'

import {registerDefaultItemTypes} from '#/plugin/exo/items/item-types'
import {registerDefaultContentItemTypes} from '#/plugin/exo/contents/content-types'

import {MODAL_ADD_ITEM,       AddItemModal}       from '#/plugin/exo/quiz/editor/components/modal/add-item-modal'
import {MODAL_IMPORT_ITEMS,   ImportItemsModal}   from '#/plugin/exo/quiz/editor/components/modal/import-items-modal'
import {MODAL_ADD_CONTENT,    AddContentModal}    from '#/plugin/exo/quiz/editor/components/modal/add-content-modal'
import {MODAL_MOVE_ITEM,      MoveItemModal}      from '#/plugin/exo/quiz/editor/components/modal/move-item-modal'
import {MODAL_CONTENT,        ContentModal}       from '#/plugin/exo/contents/components/content-modal.jsx'
import {MODAL_DUPLICATE_ITEM, DuplicateItemModal} from '#/plugin/exo/items/components/modal/duplicate-modal'

import {QuizResource} from '#/plugin/exo/resources/quiz/containers/resource'

registerDefaultItemTypes()
registerDefaultContentItemTypes()

registry.add(MODAL_ADD_ITEM, AddItemModal)
registry.add(MODAL_IMPORT_ITEMS, ImportItemsModal)
registry.add(MODAL_ADD_CONTENT, AddContentModal)
registry.add(MODAL_CONTENT, ContentModal)
registry.add(MODAL_MOVE_ITEM, MoveItemModal)
registry.add(MODAL_DUPLICATE_ITEM, DuplicateItemModal)

/**
 * Quiz resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: QuizResource
})
