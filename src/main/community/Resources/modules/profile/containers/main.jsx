import {connect} from 'react-redux'

import {Profile as ProfileComponent} from '#/main/community/profile/components/main'
import {selectors, actions} from '#/main/community/profile/store'

const Profile = connect(
  (state) => ({
    parameters: selectors.parameters(state),
    facets: selectors.facets(state),
    loaded: selectors.loaded(state)
  }),
  (dispatch) => ({
    open() {
      dispatch(actions.open())
    },
    openFacet(id) {
      dispatch(actions.openFacet(id))
    }
  })
)(ProfileComponent)

export {
  Profile
}
