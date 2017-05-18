import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {createStore} from './store'
import {registerDefaultItemTypes} from './../items/item-types'
import Docimology from './components/docimology.jsx'

registerDefaultItemTypes()

const exerciseRaw = JSON.parse(document.getElementById('docimology').dataset.exercise)
const statsRaw = JSON.parse(document.getElementById('docimology').dataset.statistics)

const store = createStore({
  exercise: exerciseRaw,
  statistics: statsRaw
})

ReactDOM.render(
  React.createElement(
    Provider,
    {store},
    React.createElement(Docimology)
  ),
  document.getElementById('docimology')
)
