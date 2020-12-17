import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {reducer, selectors} from '#/plugin/lti/resources/lti/store'
import {LtiResource as LtiResourceComponent} from '#/plugin/lti/resources/lti/components/resource'

const LtiResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      ltiResource: selectors.ltiResource(state),
      editable: hasPermission('edit', resourceSelect.resourceNode(state))
    }),
    (dispatch) => ({
      resetForm(formData) {
        dispatch(formActions.resetForm(selectors.STORE_NAME+'.ltiResourceForm', formData))
      }
    })
  )(LtiResourceComponent)
)

export {
  LtiResource
}
