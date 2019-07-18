import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {withRouter} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, reducer} from '#/main/core/administration/integration/apitoken/store'
import {ApiToken as ApiTokenComponent}  from '#/main/core/administration/integration/apitoken/components/tool'

const ApiToken = withRouter(
  withReducer('api_tokens', reducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state)
      }),
      (dispatch) => ({
        openForm(id = null) {
          dispatch(actions.open('api_tokens.token', id, {
            id: makeId()
          }))
        },
        resetForm() {
          dispatch(actions.open('api_tokens.token', null, {}))
        }
      })
    )(ApiTokenComponent)
  )
)

export {
  ApiToken
}
