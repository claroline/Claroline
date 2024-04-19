import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as baseSelectors} from '#/main/core/tool/editor/store/selectors'

const FORM_NAME = baseSelectors.STORE_NAME
const form =  state => formSelectors.form(state, FORM_NAME)

const facets = createSelector(
  [form],
  (form) => formSelectors.value(form, 'profile') || []
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
  FORM_NAME,
  facets,
  currentFacetIndex,
  currentFacet,
  allFields
}
