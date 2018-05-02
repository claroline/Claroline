
const facets = state => state.facets
const currentFacet = state => state.facets.find(facet => facet.id === state.currentFacet) || {}
const parameters = state => state.parameters

export const select = {
  facets,
  currentFacet,
  parameters
}
