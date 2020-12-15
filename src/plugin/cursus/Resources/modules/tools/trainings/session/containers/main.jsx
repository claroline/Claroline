import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {SessionMain as SessionMainComponent} from '#/plugin/cursus/tools/trainings/session/components/main'
import {selectors} from '#/plugin/cursus/tools/trainings/session/store'

const SessionMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME))
    }
  })
)(SessionMainComponent)

export {
  SessionMain
}
