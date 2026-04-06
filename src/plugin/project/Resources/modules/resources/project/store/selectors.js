import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_project'

const resource = (state) => state[STORE_NAME]

const project = createSelector(
  [resource],
  (resource) => resource.project
)

const mySubmissions = createSelector(
  [resource],
  (resource) => resource.mySubmissions || []
)

const myCorrections = createSelector(
  [resource],
  (resource) => resource.myCorrections || []
)

export const selectors = {
  STORE_NAME,
  resource,
  project,
  mySubmissions,
  myCorrections
}
