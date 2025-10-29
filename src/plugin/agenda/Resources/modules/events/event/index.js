
import {hasPermission} from '#/main/app/security/permissions'
import {declareEvent} from '#/plugin/agenda/event'

import {EventFormModal} from '#/plugin/agenda/events/event/modals/form/components/modal'
import {EventAboutModal} from '#/plugin/agenda/events/event/modals/about/components/modal'
import {EventShow} from '#/plugin/agenda/events/event/components/show'

export default declareEvent({
  name: 'event',
  icon: 'fa fa-fw fa-calendar',
  canCreate: (contextType, contextData, contextTools) => {
    const agendaTool = contextTools.find(tool => 'agenda' === tool.name)
    if (agendaTool) {
      return hasPermission('edit', agendaTool)
    }

    return false
  },
  components: {
    show: EventShow
  },
  modals: {
    form: EventFormModal,
    about: EventAboutModal
  }
})
