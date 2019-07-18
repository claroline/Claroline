import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LtiTool as LtiToolComponent}  from '#/plugin/lti/administration/lti/components/tool'
import {actions, reducer} from '#/plugin/lti/administration/lti/store'

const LtiTool = withRouter(
  withReducer('lti', reducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state)
      }),
      (dispatch) => ({
        openForm(id = null) {
          dispatch(actions.open('lti.app', id, {
            id: makeId()
          }))
        },
        resetForm() {
          dispatch(actions.open('lti.app', null, {}))
        }
      })
    )(LtiToolComponent)
  )
)

export {
  LtiTool
}
