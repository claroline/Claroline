import {reducer} from '#/plugin/cursus/catalog/session/store'
import {Session} from '#/plugin/cursus/catalog/session/components/session'

export const App = () => ({
  component: Session,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData)
})