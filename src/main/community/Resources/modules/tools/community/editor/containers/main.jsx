import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {selectors as parametersSelectors} from '#/main/core/tool/editor/store'

import {selectors} from '#/main/community/tools/community/store'
import {CommunityEditor as CommunityEditorComponent} from '#/main/community/tools/community/editor/components/main'

const CommunityEditor = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    parameters: selectors.parameters(state),
    profile: selectors.profile(state)
  }),
  (dispatch) => ({
    load(parameters, profile) {
      console.log('load')
      console.log(parameters)
      console.log(profile)
      dispatch(formActions.load(parametersSelectors.STORE_NAME, {parameters: parameters, profile: profile}))
    },
    updateProp(prop, value) {
      dispatch(formActions.updateProp(parametersSelectors.STORE_NAME, 'parameters.'+prop, value))
    }
  })
)(CommunityEditorComponent)

export {
  CommunityEditor
}