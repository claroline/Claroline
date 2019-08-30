import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {FooterMain as FooterMainComponent} from '#/main/app/layout/footer/components/main'
import {selectors, reducer} from '#/main/app/layout/footer/store'

const FooterMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      showLocale: selectors.showLocale(state),
      locale: selectors.locale(state),
      content: selectors.content(state)
    })
  )(FooterMainComponent)
)

export {
  FooterMain
}
