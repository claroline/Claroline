import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {createStore} from './store'
import {registerDefaultItemTypes} from './../items/item-types'
import Docimology from './components/docimology.jsx'

import './style.css'

registerDefaultItemTypes()

const exerciseRaw = JSON.parse(document.getElementById('docimology').dataset.exercise)

const store = createStore({
  exercise: exerciseRaw,
  currentObject: {
    type: 'exercise',
    id: exerciseRaw.id
  }
})

ReactDOM.render(
  React.createElement(
    Provider,
    {store},
    React.createElement(Docimology)
  ),
  document.getElementById('docimology')
)
