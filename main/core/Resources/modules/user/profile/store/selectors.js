import {selectors as select} from '#/main/core/tools/community/store/selectors'
import {createSelector} from 'reselect'

const store = (state) => state[select.STORE_NAME]
const FORM_NAME = select.STORE_NAME + '.profile.user'

const facets = createSelector(
  [store],
  (store) => {
    return store.profile.facets
  }
)

const loaded = createSelector(
  [store],
  (store) => {
    return store.profile.loaded
  }
)

const currentFacet = createSelector(
  [store],
  (store) => {
    return store.profile.facets.find(facet => facet.id === store.profile.currentFacet) || {}
  }
)

const parameters = createSelector(
  [store],
  (store) => store.profile.parameters
)

export const selectors = {
  facets,
  currentFacet,
  parameters,
  loaded,
  FORM_NAME
}
