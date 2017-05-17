import {createSelector} from 'reselect'

const workspaces = state => state.workspaces

const data = createSelector(
  [workspaces],
  (workspaces) => workspaces.data
)

const totalResults = createSelector(
  [workspaces],
  (workspaces) => workspaces.totalResults
)

export const select = {
  workspaces,
  data,
  totalResults
}
