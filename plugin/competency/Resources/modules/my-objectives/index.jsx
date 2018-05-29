import {bootstrap} from '#/main/app/bootstrap'

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
    competency: competencyReducers
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