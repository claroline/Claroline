
import {LayoutMain} from '#/main/app/layout/containers/main'
import {reducer} from '#/main/app/layout/store'

export default {
  component: LayoutMain,
  store: reducer,
  initialData: (initialData) => ({
    maintenance: initialData.maintenance,
    header: initialData.header,
    footer: initialData.footer,
    config: initialData.config,
    security: {
      impersonated: initialData.impersonated,
      currentUser: initialData.currentUser,
      administration: initialData.administration,
      client: initialData.client
    }
  })
}
