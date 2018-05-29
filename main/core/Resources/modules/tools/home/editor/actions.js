import {trans} from '#/main/core/translation'
import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ADD_WIDGET} from '#/main/core/widget/modals/components/add-widget'
import {MODAL_EDIT_WIDGET} from '#/main/core/widget/modals/components/edit-widget'

export const WIDGET_ADD    = 'WIDGET_ADD'
export const WIDGET_REMOVE = 'WIDGET_REMOVE'

export const actions = {}

actions.addWidget = makeActionCreator(WIDGET_ADD, 'position', 'widget')
actions.updateWidget = makeActionCreator(WIDGET_ADD, 'position', 'widget')
actions.removeWidget = makeActionCreator(WIDGET_REMOVE, 'position')

// todo : cache available widgets
actions.insertWidget = (context, position) => ({
  [API_REQUEST]: {
    url: ['apiv2_widget_available', {context: 'desktop'}],
    success: (response, dispatch) => dispatch(modalActions.showModal(MODAL_ADD_WIDGET, {
      availableWidgets: response,
      add: (widgetType) => dispatch(actions.createWidget(position, widgetType.name))
    }))
  }
})

actions.createWidget = (position, widgetType) => (dispatch) => dispatch(modalActions.showModal(MODAL_EDIT_WIDGET, {
  data: {
    type: widgetType
  },
  save: (updated) => dispatch(actions.addWidget(position, updated))
}))

actions.editWidget = (position, widget) => (dispatch) => dispatch(modalActions.showModal(MODAL_EDIT_WIDGET, {
  data: widget,
  save: (updated) => dispatch(actions.updateWidget(position, updated))
}))

actions.deleteWidget = (position) => (dispatch) => dispatch(modalActions.showModal(MODAL_CONFIRM, {
  icon: 'fa fa-fw fa-trash-o',
  title: trans('widget_delete_confirm_title', {}, 'widget'),
  question: trans('widget_delete_confirm_message', {}, 'widget'),
  dangerous: true,
  handleConfirm: () => dispatch(actions.removeWidget(position))
}))
