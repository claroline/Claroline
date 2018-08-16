import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {BookReferenceResource as BookReferenceResourceComponent} from '#/plugin/bibliography/resources/book-reference/components/resource'
import {reducer, selectors} from '#/plugin/bibliography/resources/book-reference/store'

const BookReferenceResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      canEdit: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(BookReferenceResourceComponent)
)

export {
  BookReferenceResource
}
