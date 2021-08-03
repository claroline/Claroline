import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors, reducer} from '#/main/core/tool/store'
import {ValidationMain as ValidationComponent} from '#/plugin/cursus/tools/trainings/validation/components/main'

const ValidationMain = withReducer(selectors.STORE_NAME, reducer)(
	connect(
    	(state) => ({
      		path: toolSelectors.path(state),
		})
	)(ValidationComponent)
)

export {
  ValidationMain
}
