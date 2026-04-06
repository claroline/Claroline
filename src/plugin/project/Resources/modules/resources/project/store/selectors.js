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

const myDeadline = createSelector(
  [resource],
  (resource) => resource.myDeadline || null
)

/**
 * Returns milestones sorted by position from the project configuration.
 */
const milestones = createSelector(
  [project],
  (project) => (project.milestones || []).sort((a, b) => a.position - b.position)
)

export const selectors = {
  STORE_NAME,
  resource,
  project,
  mySubmissions,
  myCorrections,
  myDeadline,
  milestones
}
