
const termOfService = (state) => state.termOfService
const options = (state) => state.options
const facets = (state) => state.facets
const workspaces = (state) => state.workspaces
const defaultWorkspaces = (state) => state.defaultWorkspaces

export const select = {
  termOfService,
  options,
  facets,
  workspaces,
  defaultWorkspaces
}
