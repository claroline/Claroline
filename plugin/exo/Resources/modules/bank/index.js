import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'

import {createStore} from './store'
import {registerDefaultItemTypes} from './../items/item-types'
import {registerModalType} from '#/main/core/layout/modal'
import {MODAL_ADD_ITEM, AddItemModal} from './../quiz/editor/components/modal/add-item-modal.jsx'
import {MODAL_SEARCH, SearchModal} from './components/modal/search.jsx'
import {MODAL_SHARE, ShareModal} from './components/modal/share.jsx'
import {Bank} from './components/bank.jsx'

// Load question types
registerDefaultItemTypes()

// Register needed modals
registerModalType(MODAL_SEARCH, SearchModal)
registerModalType(MODAL_ADD_ITEM, AddItemModal)
registerModalType(MODAL_SHARE, ShareModal)

// Get initial data
const container = document.getElementById('questions-bank')
const initialData = JSON.parse(container.dataset['initial'])
const currentUser = JSON.parse(container.dataset['user'])

const store = createStore(Object.assign({}, initialData, {
  currentUser
}))

ReactDOM.render(
  React.createElement(
    Provider,
    {store},
    React.createElement(Bank)
  ),
  document.getElementById('questions-bank')
)
