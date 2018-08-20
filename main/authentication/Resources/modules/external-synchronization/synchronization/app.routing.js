/**
 * Created by panos on 5/30/17.
 */
export const states = [
  {
    'name': 'user-list',
    'url': '/users',
    'template': '<user-list on-alert="$ctrl.pushAlert($alert)"></user-list>'
  },
  {
    'name': 'group-list',
    'url': '/groups',
    'template': '<group-list on-alert="$ctrl.pushAlert($alert)"></group-list>'
  }
]