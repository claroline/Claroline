import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {reducer, selectors} from '#/plugin/scorm/resources/scorm/store'
import {ScormResource as ScormResourceComponent} from '#/plugin/scorm/resources/scorm/components/resource'

const ScormResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        scorm: selectors.scorm(state),
        editable: hasPermission('edit', resourceSelect.resourceNode(state))
      }),
      (dispatch) => ({
        resetForm(formData) {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.scormForm', formData))
        }
      })
    )(ScormResourceComponent)
  )
)

export {
  ScormResource
}
