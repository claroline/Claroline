
import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/community/tools/community/profile/store'
import {ProfileMain as ProfileMainComponent} from '#/main/community/tools/community/profile/components/main'

const ProfileMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    facets: selectors.facets(state)
  }),
  (dispatch) => ({
    openFacet(id) {
      dispatch(actions.openFacet(id))
    },
    addFacet() {
      dispatch(actions.addFacet())
    },
    removeFacet(facet) {
      dispatch(actions.removeFacet(facet.id))
    }
  })
)(ProfileMainComponent)

export {
  ProfileMain
}
