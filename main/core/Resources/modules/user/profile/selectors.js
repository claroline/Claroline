
const facets = state => state.facets
const currentFacet = state => state.facets.find(facet => facet.id === state.currentFacet) || {}

export const select = {
  facets,
  currentFacet
}
