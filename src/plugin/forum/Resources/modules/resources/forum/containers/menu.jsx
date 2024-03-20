import {connect} from 'react-redux'

import {ForumMenu as ForumMenuComponent} from '#/plugin/forum/resources/forum/components/menu'
import {selectors} from '#/plugin/forum/resources/forum/store'

const ForumMenu = connect(
  (state) => ({
    overview: selectors.overview(state),
    moderator: selectors.moderator(state)
  })
)(ForumMenuComponent)

export {
  ForumMenu
}
