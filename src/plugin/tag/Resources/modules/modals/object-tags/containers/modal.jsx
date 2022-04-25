import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {param} from '#/main/app/config'

import {ObjectTagsModal as ObjectTagsModalComponent} from '#/plugin/tag/modals/object-tags/components/modal'
import {actions, reducer, selectors} from '#/plugin/tag/modals/object-tags/store'

const ObjectTagsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      tags: selectors.tags(state),
      canCreate: param('canCreateTags')
    }),
    (dispatch) => ({
      loadTags(objectClass, objects) {
        dispatch(actions.fetchTags(objectClass, objects))
      },
      addTag(objectClass, objects, tag) {
        return dispatch(actions.postTag(objectClass, objects, tag))
      },
      removeTag(objectClass, objects, tag) {
        return dispatch(actions.removeTag(objectClass, objects, tag))
      }
    })
  )(ObjectTagsModalComponent)
)

export {
  ObjectTagsModal
}
