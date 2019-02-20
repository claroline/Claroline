import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security/permissions'

import {RssFeedResource as RssFeedResourceComponent} from '#/plugin/rss/resources/rss-feed/components/resource'
import {reducer, selectors} from '#/plugin/rss/resources/rss-feed/store'

const RssFeedResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        editable: hasPermission('edit', resourceSelectors.resourceNode(state))
      })
    )(RssFeedResourceComponent)
  )
)

export {
  RssFeedResource
}
