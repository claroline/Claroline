import {MainView} from './components/main-view.jsx'
import {CompetencyView} from './components/competency-view.jsx'
import {
  VIEW_MAIN,
  VIEW_COMPETENCY
} from './enums'

export const viewComponents = {
  [VIEW_MAIN]: MainView,
  [VIEW_COMPETENCY]: CompetencyView
}
