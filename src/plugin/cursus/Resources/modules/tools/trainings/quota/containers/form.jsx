import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {QuotaForm as QuotaFormComponent} from '#/plugin/cursus/tools/trainings/quota/components/form'

const QuotaForm = connect(
  (state) => ({
    currentContext: toolSelectors.context(state),
    isNew: formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME)),
    quota: formSelectors.originalData(formSelectors.form(state, selectors.FORM_NAME))
  })
)(QuotaFormComponent)

export {
  QuotaForm
}
