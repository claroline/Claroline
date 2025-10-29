
import {hasPermission} from '#/main/app/security/permissions'
import {declareEvent} from '#/plugin/agenda/event'

import {TaskFormModal} from '#/plugin/agenda/events/task/modals/form/components/modal'
import {TaskAboutModal} from '#/plugin/agenda/events/task/modals/about/components/modal'
import {TaskShow} from '#/plugin/agenda/events/task/components/show'

export default declareEvent({
  name: 'task',
  icon: 'fa fa-fw fa-tasks',
  canCreate: (contextType, contextData, contextTools) => {
    const agendaTool = contextTools.find(tool => 'agenda' === tool.name)
    if (agendaTool) {
      return hasPermission('edit', agendaTool)
    }

    return false
  },
  components: {
    show: TaskShow
  },
  modals: {
    about: TaskAboutModal,
    form: TaskFormModal
  }
})
