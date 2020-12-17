import {createSelector} from 'reselect'

const STORE_NAME = 'my-learning-objectives'

const store = (state) => state[STORE_NAME]

const objectives = createSelector(
  [store],
  (store) => store.objectives
)

const competencies = createSelector(
  [store],
  (store) => store.competencies
)

const objectivesCompetencies = createSelector(
  [store],
  (store) => store.objectivesCompetencies
)

const competency = createSelector(
  [store],
  (store) => store.competency
)

export const selectors = {
  STORE_NAME,
  store,
  objectives,
  competencies,
  objectivesCompetencies,
  competency
}
