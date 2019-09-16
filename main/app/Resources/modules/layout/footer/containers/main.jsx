import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'

import {FooterMain as FooterMainComponent} from '#/main/app/layout/footer/components/main'
import {selectors, reducer} from '#/main/app/layout/footer/store'

const FooterMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      // footer configuration
      display: selectors.display(state),
      content: selectors.content(state),

      // platform parameters
      version: configSelectors.param(state, 'version'),
      helpUrl: configSelectors.param(state, 'helpUrl'),
      locale: configSelectors.param(state, 'locale')
    })
  )(FooterMainComponent)
)

export {
  FooterMain
}
