import {connect} from 'react-redux'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ValidationDetail as ValidationComponent} from '#/plugin/cursus/tools/trainings/quota/validation/components/detail'

const ValidationDetail = connect(
	(state) => ({
    	currentContext: toolSelectors.context(state),
		quota: selectors.quota(state)
	})
)(ValidationComponent)

export {
  ValidationDetail
}
