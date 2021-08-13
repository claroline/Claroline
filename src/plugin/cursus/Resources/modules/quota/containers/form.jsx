import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {QuotaForm as QuotaFormComponent} from '#/plugin/cursus/quota/components/form'

const QuotaForm = connect(
  (state, ownProps) =>({
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    quota: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch) => ({
    update(name, prop, value) {
      dispatch(formActions.updateProp(name, prop, value))
    }
  })
)(QuotaFormComponent)

export {
  QuotaForm
}
