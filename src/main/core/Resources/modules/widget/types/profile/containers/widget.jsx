import {connect} from 'react-redux'

import {ProfileWidget as ProfileWidgetComponent} from '#/main/core/widget/types/profile/components/widget'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const ProfileWidget = connect(
  (state) => ({
    user: contentSelectors.parameters(state).user
  })
)(ProfileWidgetComponent)

export {
  ProfileWidget
}
