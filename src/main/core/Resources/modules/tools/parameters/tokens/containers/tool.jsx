import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/core/tools/parameters/store/selectors'
import {TokensTool as TokensToolComponent} from '#/main/core/tools/parameters/tokens/components/tool'
import {actions} from '#/main/core/tools/parameters/tokens/store/actions'

const TokensTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id) {
      dispatch(actions.open(selectors.STORE_NAME+'.tokens.current', id))
    }
  })
)(TokensToolComponent)

export {
  TokensTool
}
