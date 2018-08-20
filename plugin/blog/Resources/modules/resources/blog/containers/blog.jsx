import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {reducer, selectors} from '#/plugin/blog/resources/blog/store'
import {Blog} from '#/plugin/blog/resources/blog/player/components/resource'

const BlogResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      blogId: selectors.blog(state).data.id,
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME + '.blog.data.options')),
      pdfEnabled: selectors.pdfEnabled(state),
      canExport: hasPermission('export', resourceSelect.resourceNode(state)),
      canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
      canPost: hasPermission('post', resourceSelect.resourceNode(state)),
      canModerate: hasPermission('moderate', resourceSelect.resourceNode(state))
    })
  )(Blog)
)

export {
  BlogResource
}
