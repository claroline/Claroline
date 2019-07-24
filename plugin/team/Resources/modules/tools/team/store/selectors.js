import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_team_tool'

const store = (state) => state[STORE_NAME]

const teamParams = createSelector(
  [store],
  (store) => store.teamParams
)

const allowedTeams = createSelector(
  [teamParams],
  (teamParams) => teamParams.allowedTeams
)

const canEdit = createSelector(
  [store],
  (store) => store.canEdit
)

const myTeams = createSelector(
  [store],
  (store) => store.myTeams
)

const resourceTypes = createSelector(
  [store],
  (store) => store.resourceTypes
)

export const selectors = {
  STORE_NAME,
  store,
  teamParams,
  allowedTeams,
  canEdit,
  myTeams,
  resourceTypes
}