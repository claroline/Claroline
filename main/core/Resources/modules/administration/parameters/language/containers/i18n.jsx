import {connect} from 'react-redux'

import {selectors} from '#/main/core/administration/parameters/store'
import {I18n as I18nComponent} from '#/main/core/administration/parameters/language/components/i18n'

const I18n = connect(
  (state) => ({
    availableLocales: selectors.availableLocales(state)
  })
)(I18nComponent)

export {
  I18n
}
