import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {DragDropContext} from 'react-dnd'
import {default as TouchBackend} from 'react-dnd-touch-backend'
import {createStore} from '#/main/core/utilities/redux'
import {registerModalTypes} from '#/main/core/layout/modal'
import {DeleteConfirmModal} from '#/main/core/layout/modal/components/delete-confirm.jsx'
import {makeRouter} from './router'
import {reducers} from './reducers'
import {VIEW_MANAGER, VIEW_USER} from './enums'
import {EventFormModal} from './components/event-form-modal.jsx'
import {EventRepeatFormModal} from './components/event-repeat-form-modal.jsx'
import {SessionEventsToolLayout} from './components/session-events-tool-layout.jsx'
import {EventCommentsModal} from './components/event-comments-modal.jsx'

class SessionEventsTool {
  constructor(workspaceId, canEdit, sessions, events, eventsUsers) {
    registerModalTypes([
      ['DELETE_MODAL', DeleteConfirmModal],
      ['MODAL_EVENT_FORM', EventFormModal],
      ['MODAL_EVENT_REPEAT_FORM', EventRepeatFormModal],
      ['MODAL_EVENT_COMMENTS', EventCommentsModal]
    ])
    const sessionId = sessions.length === 1 ? sessions[0]['id'] : null
    this.viewMode = canEdit ? VIEW_MANAGER : VIEW_USER
    this.store = createStore(
      reducers,
      {
        workspaceId: workspaceId,
        canEdit: canEdit,
        sessions: sessions,
        sessionId: sessionId,
        events: {
          data: events,
          totalResults: eventsTotal
        },
        eventsUsers: eventsUsers,
        viewMode: this.viewMode
      }
    )
    this.dndSessionEventsTool = DragDropContext(TouchBackend({enableMouseEvents: true}))(SessionEventsToolLayout)
    makeRouter(this.store.dispatch.bind(this.store))
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(this.dndSessionEventsTool)
      ),
      element
    )
  }
}

const container = document.querySelector('.session-events-tool-container')
const workspaceId = parseInt(container.dataset.workspace)
const canEdit = parseInt(container.dataset.editable)
const sessions = JSON.parse(container.dataset.sessions)
const events = JSON.parse(container.dataset.events)
const eventsTotal = parseInt(container.dataset.eventsTotal)
const eventsUsers = JSON.parse(container.dataset.eventsUsers)
const tool = new SessionEventsTool(workspaceId, canEdit, sessions, events, eventsUsers)

tool.render(container)