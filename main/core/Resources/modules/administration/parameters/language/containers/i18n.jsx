import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/core/administration/parameters/store'
import {I18n as I18nComponent} from '#/main/core/administration/parameters/language/components/i18n'

const I18n = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    availableLocales: selectors.availableLocales(state)
  })
)(I18nComponent)

export {
  I18n
}
