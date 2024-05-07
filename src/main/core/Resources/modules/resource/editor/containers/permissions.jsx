import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {ResourceEditorPermissions as ResourceEditorPermissionsComponent} from '#/main/core/resource/editor/components/permissions'
import {reducer, selectors, actions} from '#/main/core/resource/editor/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

const ResourceEditorPermissions = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      resourceNode: resourceSelectors.resourceNode(state),
      rights: formSelectors.value(formSelectors.form(state, resourceSelectors.EDITOR_NAME), 'rights')
      //recursiveEnabled: selectors.recursiveEnabled(state)
    }),
    (dispatch) => ({
      loadRights(resourceNode) {
        return dispatch(actions.fetchRights(resourceNode)).then((rights) => {
          dispatch(formActions.load(resourceSelectors.EDITOR_NAME, {rights: rights}))
        })
      },
      updateRights(perms) {
        dispatch(formActions.updateProp(resourceSelectors.EDITOR_NAME, 'rights', perms))
      },
      setRecursiveEnabled(bool) {
        dispatch(actions.setRecursive(bool))
      }
    })
  )(ResourceEditorPermissionsComponent)
)

export {
  ResourceEditorPermissions
}
