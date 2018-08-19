import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {select} from '#/plugin/blog/resources/blog/selectors'
import {reducer} from '#/plugin/blog/resources/blog/store/reducer'
import {Blog} from '#/plugin/blog/resources/blog/player/components/resource'

const BlogResource = withReducer(select.STORE_NAME, reducer)(
  connect(
    state => ({
      blogId: select.blog(state).data.id,
      saveEnabled: formSelect.saveEnabled(formSelect.form(state, select.STORE_NAME + '.blog.data.options')),
      pdfEnabled: select.pdfenabled(state),
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
