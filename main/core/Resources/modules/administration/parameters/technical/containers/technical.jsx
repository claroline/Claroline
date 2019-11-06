import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {Technical as TechnicalComponent} from '#/main/core/administration/parameters/technical/components/technical'

const Technical = connect(
  (state) => ({
    path: toolSelectors.path(state),
    toolChoices: selectors.toolChoices(state),
    mailer: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)).mailer
  })
)(TechnicalComponent)

export {
  Technical
}
