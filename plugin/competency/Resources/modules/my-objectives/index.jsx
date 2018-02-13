import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {reducer as modalReducer}    from '#/main/core/layout/modal/reducer'

import {
  objectivesReducers,
  competenciesReducers,
  competencyReducers
} from './reducers'
import {MyObjectivesTool} from './components/tool.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.my-objectives-tool-container',

  // app main component
  MyObjectivesTool,

  // app store configuration
  {
    // app reducers
    objectives: objectivesReducers,
    objectivesCompetencies: objectivesReducers,
    competencies: competenciesReducers,
    competency: competencyReducers,

    // generic reducers
    modal: modalReducer
  },

  // transform data attributes for redux store
  (initialData) => {
    return {
      objectives: initialData.objectives,
      objectivesCompetencies: initialData.objectivesCompetencies,
      competencies: initialData.competencies
    }
  }
)