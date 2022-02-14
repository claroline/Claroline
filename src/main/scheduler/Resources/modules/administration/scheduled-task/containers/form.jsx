import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {UserList} from '#/main/core/administration/community/user/components/user-list'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ScheduledTaskForm as ScheduledTaskFormComponent} from '#/main/scheduler/administration/scheduled-task/components/form'
import {actions, selectors} from '#/main/scheduler/administration/scheduled-task/store'

const ScheduledTaskForm = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.task')),
    task: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.task'))
  }),
  dispatch =>({
    pickUsers(taskId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add', {}, 'actions'),
        name: selectors.STORE_NAME + '.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(taskId, selected))
      }))
    }
  })
)(ScheduledTaskFormComponent)

export {
  ScheduledTaskForm
}
