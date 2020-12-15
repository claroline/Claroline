import {createSelector} from 'reselect'

const STORE_NAME = 'competencyResourcesLinksModal'

const resourcesLinks = (state) => state[STORE_NAME]

const competencies = createSelector(
  [resourcesLinks],
  (resourcesLinks) => resourcesLinks.competencies
)

const abilities = createSelector(
  [resourcesLinks],
  (resourcesLinks) => resourcesLinks.abilities
)

export const selectors = {
  STORE_NAME,
  resourcesLinks,
  competencies,
  abilities
}
