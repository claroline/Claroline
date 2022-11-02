import {createSelector} from 'reselect'

const STORE_NAME = 'userProfile'
const FORM_NAME = STORE_NAME + '.user'

const store = (state) => state[STORE_NAME]

const facets = createSelector(
  [store],
  (store) => {
    return store.facets
  }
)

const loaded = createSelector(
  [store],
  (store) => {
    return store.loaded
  }
)

const currentFacet = createSelector(
  [store],
  (store) => {
    return store.facets.find(facet => facet.id === store.currentFacet) || {}
  }
)

const parameters = createSelector(
  [store],
  (store) => store.parameters
)

const allFields = createSelector(
  [facets],
  (configuredFacets) => {
    let fields = []

    configuredFacets.map(facet => facet.sections.map(section => {
      fields = fields.concat(section.fields)
    }))

    return fields
  }
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  facets,
  currentFacet,
  parameters,
  loaded,
  allFields
}
