import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

import {HomeMain as HomeMainComponent} from '#/main/app/layout/sections/home/components/main'
import {selectors} from '#/main/app/layout/sections/home/store'

const HomeMain = connect(
  (state) => ({
    maintenance: layoutSelectors.maintenance(state),
    hasHome: selectors.hasHome(state),
    homeType: selectors.homeType(state),
    homeData: selectors.homeData(state),
    isAuthenticated: securitySelectors.isAuthenticated(state)
  })
)(HomeMainComponent)

export {
  HomeMain
}
