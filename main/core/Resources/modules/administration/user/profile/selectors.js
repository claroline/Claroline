import {createSelector} from 'reselect'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

const formName = 'profile'
const form =  state => formSelect.form(state, formName)

const facets = createSelector(
  [form],
  (form) => formSelect.data(form)
)

const currentFacetId = createSelector(
  [form],
  (form) => form.currentFacet
)

const currentFacetIndex = createSelector(
  [facets, currentFacetId],
  (facets, currentFacetId) => facets.findIndex(facet => facet.id === currentFacetId)
)

const currentFacet = createSelector(
  [facets, currentFacetIndex],
  (facets, currentFacetIndex) => -1 !== currentFacetIndex ? facets[currentFacetIndex] : {}
)

export const select = {
  formName,
  facets,
  currentFacetIndex,
  currentFacet
}
