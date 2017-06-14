/**
 * Created by panos on 5/30/17.
 */
import userListTemplate from './user-list.partial.html'
import { UserListController } from './user-list.controller'

export const UserListComponent = {
  template: userListTemplate,
  controller: UserListController,
  bindings: {
    'onAlert': '&'
  }
}