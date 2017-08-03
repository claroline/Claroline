import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {createStore} from '#/main/core/utilities/redux'
import {makeRouter} from './router'
import {reducers} from './reducers'
import {VIEW_MAIN} from './enums'
import {MyObjectivesToolLayout} from './components/my-objectives-tool-layout.jsx'

class MyObjectivesTool {
  constructor(objectives, objectivesCompetencies, competencies) {
    this.store = createStore(
      reducers,
      {
        objectives: objectives,
        objectivesCompetencies: objectivesCompetencies,
        competencies: competencies,
        viewMode: VIEW_MAIN
      }
    )
    makeRouter(this.store.dispatch.bind(this.store))
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(MyObjectivesToolLayout)
      ),
      element
    )
  }
}

const container = document.querySelector('.my-objectives-tool-container')
const objectives = JSON.parse(container.dataset.objectives)
const objectivesCompetencies = JSON.parse(container.dataset.objectivesCompetencies)
const competencies = JSON.parse(container.dataset.competencies)
const tool = new MyObjectivesTool(objectives, objectivesCompetencies, competencies)

tool.render(container)