import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource'

import {AnnouncementOverview as AnnouncementOverviewComponent} from '#/plugin/announcement/resources/announcement/components/overview'
import {selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncementOverview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    posts: selectors.sortedPosts(state)
  })
)(AnnouncementOverviewComponent)

export {
  AnnouncementOverview
}
