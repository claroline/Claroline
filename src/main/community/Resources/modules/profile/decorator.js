import cloneDeep from 'lodash/cloneDeep'

import {
  getDefaultFacet,
  getMainFacet
} from '#/main/community/profile/utils'

/**
 *
 * @param {Array} profileFacets
 *
 * @return {Array}
 */
function decorate(profileFacets = []) {
  const decoratedFacets = cloneDeep(profileFacets)

  // no facet at all
  if (0 === decoratedFacets.length) {
    decoratedFacets.push(
      getDefaultFacet()
    )
  }

  // no main facet
  if (undefined === getMainFacet(decoratedFacets)) {
    // transform the first facet into the main
    decoratedFacets[0].meta.main = true
  }

  return decoratedFacets
}

export {
  decorate
}
