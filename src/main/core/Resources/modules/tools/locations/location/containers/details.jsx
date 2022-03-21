import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationDetails as LocationDetailsComponent} from '#/main/core/tools/locations/location/components/details'
import {actions, selectors} from '#/main/core/tools/locations/location/store'

const LocationDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    location: selectors.currentLocation(state)
  }),
  (dispatch) => ({
    addUsers(id, users) {
      dispatch(actions.addUsers(id, users))
    },
    addOrganizations(id, organizations) {
      dispatch(actions.addOrganizations(id, organizations))
    },
    addGroups(id, groups) {
      dispatch(actions.addGroups(id, groups))
    }
  })
)(LocationDetailsComponent)

export {
  LocationDetails
}
