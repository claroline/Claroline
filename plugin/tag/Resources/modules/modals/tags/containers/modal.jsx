import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {TagsModal as TagsModalComponent} from '#/plugin/tag/modals/tags/components/modal'
import {actions, reducer, selectors} from '#/plugin/tag/modals/tags/store'

const TagsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      tags: selectors.tags(state)
    }),
    (dispatch) => ({
      loadTags(objectClass, objects) {
        dispatch(actions.fetchTags(objectClass, objects))
      },
      addTag(objectClass, objects, tag) {
        dispatch(actions.postTag(objectClass, objects, tag))
      },
      removeTag(objectClass, objects, tag) {
        dispatch(actions.removeTag(objectClass, objects, tag))
      }
    })
  )(TagsModalComponent)
)

export {
  TagsModal
}
