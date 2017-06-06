import {ManagerView} from './components/manager-view.jsx'
import {UserView} from './components/user-view.jsx'
import {EventView} from './components/event-view.jsx'
import {VIEW_MANAGER, VIEW_USER, VIEW_EVENT} from './enums'

export const viewComponents = {
  [VIEW_MANAGER]: ManagerView,
  [VIEW_USER]: UserView,
  [VIEW_EVENT]: EventView
}
