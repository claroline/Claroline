import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {reducer, selectors} from '#/plugin/blog/resources/blog/store'
import {BlogResource as BlogResourceComponent} from '#/plugin/blog/resources/blog/components/resource'

const BlogResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      blogId: selectors.blog(state).data.id,
      canExport: hasPermission('export', resourceSelect.resourceNode(state)),
      canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
      canPost: hasPermission('post', resourceSelect.resourceNode(state)),
      canModerate: hasPermission('moderate', resourceSelect.resourceNode(state))
    })
  )(BlogResourceComponent)
)

export {
  BlogResource
}
