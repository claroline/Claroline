const teamParams = state => state.teamParams
const allowedTeams = state => state.teamParams.allowedTeams
const canEdit = state => state.canEdit
const myTeams = state => state.myTeams
const resourceTypes = state => state.resourceTypes

export const selectors = {
  teamParams,
  allowedTeams,
  canEdit,
  myTeams,
  resourceTypes
}