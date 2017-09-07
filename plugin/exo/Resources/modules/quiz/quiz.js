import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {DragDropContext} from 'react-dnd'
import {default as TouchBackend} from 'react-dnd-touch-backend'
import {Quiz as QuizComponent} from './components/quiz.jsx'
import {normalize} from './normalizer'
import {decorate} from './decorators'
import {createStore} from './store'
import {makeRouter} from './router'
import {makeSaveGuard} from './editor/save-guard'
import {registerDefaultItemTypes, getDecorators} from './../items/item-types'
import {registerDefaultContentItemTypes} from './../contents/content-types'
import {registerModalTypes} from '#/main/core/layout/modal'

import {MODAL_ADD_ITEM, AddItemModal} from './editor/components/modal/add-item-modal.jsx'
import {MODAL_IMPORT_ITEMS, ImportItemsModal} from './editor/components/modal/import-items-modal.jsx'
import {MODAL_ADD_CONTENT, AddContentModal} from './editor/components/modal/add-content-modal.jsx'
import {MODAL_CONTENT, ContentModal} from './../contents/components/content-modal.jsx'
import {MODAL_MOVE_ITEM, MoveItemModal} from './editor/components/modal/move-item-modal.jsx'
import {MODAL_DUPLICATE_ITEM, DuplicateItemModal} from '#/plugin/exo/items/components/modal/duplicate-modal.jsx'

export class Quiz {
  constructor(rawQuizData, rawResourceNodeData, noServer = false) {
    registerDefaultItemTypes()
    registerDefaultContentItemTypes()

    // register modals
    registerModalTypes([
      [MODAL_ADD_ITEM, AddItemModal],
      [MODAL_IMPORT_ITEMS, ImportItemsModal],
      [MODAL_ADD_CONTENT, AddContentModal],
      [MODAL_CONTENT, ContentModal],
      [MODAL_MOVE_ITEM, MoveItemModal],
      [MODAL_DUPLICATE_ITEM, DuplicateItemModal]
    ])

    const quizData = decorate(normalize(rawQuizData), getDecorators(), rawResourceNodeData.rights.current.edit)
    // todo : editable property has been lost and so the store is always configured for edition
    this.store = createStore(Object.assign({noServer: noServer, resourceNode: rawResourceNodeData}, quizData))
    this.dndQuiz = DragDropContext(TouchBackend({ enableMouseEvents: true }))(QuizComponent)
    makeRouter(this.store.dispatch.bind(this.store))
    makeSaveGuard(this.store.getState.bind(this.store))
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(this.dndQuiz)
      ),
      element
    )
  }
}
