import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as listActions} from '#/main/app/content/list/store'

import {AgendaViewList as AgendaViewListComponent} from '#/plugin/agenda/tools/agenda/views/list/components/view'
import {selectors, reducer} from '#/plugin/agenda/tools/agenda/views/list/store'

const AgendaViewList = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      invalidate() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(AgendaViewListComponent)
)

export {
  AgendaViewList
}