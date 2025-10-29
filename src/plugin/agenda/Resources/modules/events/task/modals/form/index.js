/**
 * Agenda task form modal.
 * Displays a form to edit a task.
 */

import {registry} from '#/main/app/modals/registry'

import {TaskFormModal} from '#/plugin/agenda/events/task/modals/form/components/modal'

const MODAL_AGENDA_TASK_FORM = 'MODAL_AGENDA_TASK_FORM'

registry.add(MODAL_AGENDA_TASK_FORM, TaskFormModal)

export {
  MODAL_AGENDA_TASK_FORM
}
