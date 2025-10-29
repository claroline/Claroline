
import {hasPermission} from '#/main/app/security/permissions'
import {declareEvent} from '#/plugin/agenda/event'

import {EventShow} from '#/plugin/cursus/event/containers/show'
import {EventFormModal} from '#/plugin/cursus/event/modals/form/components/modal'
import {EventAboutModal} from '#/plugin/cursus/event/modals/about/components/modal'

export default declareEvent({
  name: 'training_event',
  icon: 'fa fa-fw fa-graduation-cap',
  canCreate: (contextType, contextData, contextTools) => {
    if ('workspace' === contextType) {
      // training events creation is only enabled in workspace
      const tool = contextTools.find(tool => 'trainings' === tool.name)
      if (tool) {
        return hasPermission('edit', tool)
      }
    }

    return false
  },
  components: {
    show: EventShow
  },
  modals: {
    about: EventAboutModal,
    form: EventFormModal
  }
})
