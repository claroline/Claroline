import {createSelector} from 'reselect'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'

const formName = baseSelectors.STORE_NAME+'.profile'
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
  (facets, currentFacetIndex) => -1 !== currentFacetIndex ? facets[currentFacetIndex] : undefined
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
  formName,
  facets,
  currentFacetIndex,
  currentFacet,
  allFields
}
