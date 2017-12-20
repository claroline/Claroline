
const user = state => state.user
const facets = state => state.facets
const currentFacet = state => state.facets.find(facet => facet.id === state.currentFacet) || {}

export const select = {
  user,
  facets,
  currentFacet
}
