import {withReducer} from '#/main/app/store/reducer'

import {ResourceDashboard as ResourceDashboardComponent} from '#/main/core/resource/dashboard/components/main'
import {reducer, selectors} from '#/main/core/resource/dashboard/store'

const ResourceDashboard = withReducer(selectors.STORE_NAME, reducer)(
  ResourceDashboardComponent
)

export {
  ResourceDashboard
}
