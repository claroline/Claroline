import {connect} from 'react-redux'

import {actions, selectors} from '#/main/core/resource/modals/creation/store'
import {FileCreation as FileCreationComponent} from '#/main/core/resources/file/components/creation'

const FileCreation = connect(
  (state) => ({
    newNode: selectors.newNode(state)
  }),
  (dispatch) => ({
    update(newNode, file) {
      // update resource props
      dispatch(actions.updateResource('size', file.size))
      dispatch(actions.updateResource('hashName', file.url))

      // update node props
      dispatch(actions.updateNode('meta.mimeType', file.mimeType))
      let cleanedName = file.name.replace('_', ' ').substring(0, file.name.lastIndexOf('.'))
      cleanedName = cleanedName.charAt(0).toUpperCase() + cleanedName.slice(1)
      if (!newNode.name) {
        // only set name if none provided
        dispatch(actions.updateNode('name', cleanedName))
      }

      if (!newNode.code) {
        // only set code if none provided
        dispatch(actions.updateNode('code', cleanedName))
      }
    }
  })
)(FileCreationComponent)

export {
  FileCreation
}
