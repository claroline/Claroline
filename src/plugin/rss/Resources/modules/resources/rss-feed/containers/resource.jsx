import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security/permissions'
import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {RssFeedResource as RssFeedResourceComponent} from '#/plugin/rss/resources/rss-feed/components/resource'
import {reducer, selectors} from '#/plugin/rss/resources/rss-feed/store'

const RssFeedResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        rssFeed: selectors.rssFeed(state),
        editable: hasPermission('edit', resourceSelectors.resourceNode(state))
      }),
      (dispatch) => ({
        resetForm(formData = null) {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.rssFeedForm', formData))
        }
      })
    )(RssFeedResourceComponent)
  )
)

export {
  RssFeedResource
}
