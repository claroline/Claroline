import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer, selectors} from '#/main/core/modals/text/store'
import {API_REQUEST} from '#/main/app/api'
import {url} from '#/main/app/api'

import {
  actions as listAction,
  select as listSelect
} from '#/main/app/content/list/store'
import {TextModal as TextModalComponent} from '#/main/core/modals/text/components/modal'

const TextModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      data: listSelect.data(state[selectors.STORE_NAME])
    }),
    (dispatch) => ({
      search(target, data) {
        dispatch({
          [API_REQUEST]: {
            request: {
              method: 'POST',
              body: JSON.stringify(
                {textSearch: data}
              )
            },
            url: url(target),
            success: (response, dispatch) => {
              dispatch(listAction.loadData(selectors.STORE_NAME, response.data, response.totalResults))
            },
            body: data
          }
        })
      }
    })
  )(TextModalComponent)
)

export {
  TextModal
}
