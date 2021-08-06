import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {selectors, actions, reducer} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ValidationMain as ValidationComponent} from '#/plugin/cursus/tools/trainings/quota/validation/components/main'

const ValidationMain = withReducer(selectors.STORE_NAME, reducer)(
	connect(
    	(state) => ({
      		path: toolSelectors.path(state),
		}),
    	(dispatch) => ({
      		open(id) {
       			dispatch(actions.open(id))
      		}
		})
	)(ValidationComponent)
)

export {
  ValidationMain
}
