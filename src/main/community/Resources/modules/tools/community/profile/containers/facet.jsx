import {connect} from 'react-redux'

import {ProfileFacet as ProfileFacetComponent} from '#/main/community/tools/community/profile/components/facet'
import {selectors} from '#/main/community/tools/community/profile/store'

const ProfileFacet = connect(
  (state) => ({
    index: selectors.currentFacetIndex(state),
    facet: selectors.currentFacet(state),
    fields: selectors.allFields(state)
  })
)(ProfileFacetComponent)

export {
  ProfileFacet
}
