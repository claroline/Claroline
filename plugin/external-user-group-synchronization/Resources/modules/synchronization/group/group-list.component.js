/**
 * Created by panos on 5/30/17.
 */
import groupListTemplate from './group-list.partial.html'
import { GroupListController } from './group-list.controller'

export const GroupListComponent = {
  template: groupListTemplate,
  controller: GroupListController,
  bindings: {
    'onAlert': '&'
  }
}