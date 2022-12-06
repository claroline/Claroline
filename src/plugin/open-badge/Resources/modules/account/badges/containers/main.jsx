import {withReducer} from '#/main/app/store/components/withReducer'

import {BadgesMain as BadgesMainComponent} from '#/plugin/open-badge/account/badges/components/main'
import {reducer, selectors} from '#/plugin/open-badge/account/badges/store'

const BadgesMain = withReducer(selectors.STORE_NAME, reducer)(
  BadgesMainComponent
)
export {
  BadgesMain
}
