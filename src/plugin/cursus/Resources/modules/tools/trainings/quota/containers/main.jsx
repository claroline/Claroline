import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {selectors, actions} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors, reducer} from '#/main/core/tool/store'
import {QuotaMain as QuotaComponent} from '#/plugin/cursus/tools/trainings/quota/components/main'

const QuotaMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      open(id) {
        dispatch(actions.open(id))
      },
      openForm(id, defaultProps) {
        dispatch(actions.openForm(id, defaultProps))
      }
    })
  )(QuotaComponent)
)
		
export {
  QuotaMain
}
		