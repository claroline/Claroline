import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {routedApp} from '#/main/core/utilities/app/router'
import {reducer as modalReducer}    from '#/main/core/layout/modal/reducer'
import {
  objectivesReducers,
  competenciesReducers,
  competencyReducers
} from './reducers'
import {MainView} from './components/main-view.jsx'
import {CompetencyView} from './components/competency-view.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.my-objectives-tool-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)

  routedApp([
    {path: '/', component: MainView, exact: true},
    {path: '/:oId/competency/:cId', component: CompetencyView}
  ]),

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