/**
 * Agenda task about modal.
 * Displays information about the task (used for integration with agenda).
 */

import {registry} from '#/main/app/modals/registry'

import {TaskAboutModal} from '#/plugin/agenda/events/task/modals/about/components/modal'

const MODAL_AGENDA_TASK_ABOUT = 'MODAL_AGENDA_TASK_ABOUT'

registry.add(MODAL_AGENDA_TASK_ABOUT, TaskAboutModal)

export {
  MODAL_AGENDA_TASK_ABOUT
}
