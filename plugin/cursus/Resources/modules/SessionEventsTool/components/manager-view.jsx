import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, t} from '#/main/core/translation'

import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/components/data'

import {withModal} from '#/main/app/overlay/modal'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_EVENT_COMMENTS} from '#/plugin/cursus/SessionEventsTool/components/event-comments-modal'
import {MODAL_EVENT_FORM} from '#/plugin/cursus/SessionEventsTool/components/event-form-modal'
import {MODAL_EVENT_REPEAT_FORM} from '#/plugin/cursus/SessionEventsTool/components/event-repeat-form-modal'
import {MODAL_EVENT_SET_FORM} from '#/plugin/cursus/SessionEventsTool/components/event-set-form-modal'

import {selectors} from '../selectors'
import {actions} from '../actions'
import {registrationTypes} from '../enums'

// todo upgrade data list
// todo change to a dump component

class ManagerView extends Component {
  constructor(props) {
    super(props)

    this.deleteSessionEvent = this.deleteSessionEvent.bind(this)
  }

  deleteSessionEvent(sessionEvent) {
    this.props.showModal(MODAL_CONFIRM, {
      icon: 'fa fa-fw fa-trash-o',
      title: `${trans('delete_session_event', {}, 'cursus')} [${sessionEvent.name}]`,
      question: trans('delete_session_event_confirm_message', {}, 'cursus'),
      dangerous: true,
      handleConfirm: () =>  {
        this.setState({modal: {fading: true}})

        this.props.deleteSessionEvent(this.props.workspaceId, sessionEvent.id)
      }
    })
  }

  deleteSessionEvents(sessionEvents) {
    this.props.showModal(MODAL_CONFIRM, {
      icon: 'fa fa-fw fa-trash-o',
      title: `${trans('delete_selected_session_events', {}, 'cursus')}`,
      question: trans('delete_selected_session_events_confirm_message', {}, 'cursus'),
      dangerous: true,
      handleConfirm: () =>  {
        this.setState({modal: {fading: true}})

        this.props.deleteSessionEvents(this.props.workspaceId, sessionEvents)
      }
    })
  }

  showEventCreationForm() {
    this.props.showModal(MODAL_EVENT_FORM, {
      mode: 'creation',
      title: `${trans('session_event_creation', {}, 'cursus')}`,
      event: {
        id: null,
        name: null,
        description: null,
        startDate: null,
        endDate: null,
        registrationType: 0,
        maxUsers: null,
        locationExtra: null
      },
      session: this.props.session,
      confirmAction: this.props.createSessionEvent
    })
  }

  showEventEditionForm(sessionEvent) {
    this.props.showModal(MODAL_EVENT_FORM, {
      mode: 'edition',
      title: trans('session_event_edition', {}, 'cursus'),
      event: sessionEvent,
      session: this.props.session,
      confirmAction: this.props.editSessionEvent
    })
  }

  showEventRepeatForm(sessionEvent) {
    this.props.showModal(MODAL_EVENT_REPEAT_FORM, {
      title: trans('repeat_session_event', {}, 'cursus'),
      event: sessionEvent,
      repeatSessionEvent: this.props.repeatSessionEvent
    })
  }

  showEventCommentsManagement(sessionEvent) {
    this.props.showModal(MODAL_EVENT_COMMENTS, {
      title:trans('informations_management', {}, 'cursus'),
      event: sessionEvent
    })
  }

  showEventSetForm(eventSet) {
    this.props.showModal(MODAL_EVENT_SET_FORM, {
      title: trans('session_event_set_edition', {}, 'cursus'),
      eventSet: eventSet
    })
  }

  render() {
    if (this.props.session) {
      return (
        <div>
          <ListData
            data={this.props.events}
            totalResults={this.props.total}
            primaryAction={(row) => ({
              type: 'link',
              target: `event/${row.id}`
            })}
            definition={[
              {
                name: 'name',
                type: 'string',
                label: t('name'),
                primary: true,
                displayed: true
              }, {
                name: 'startDate',
                type: 'date',
                label: t('start_date'),
                displayed: true,
                options: {
                  time: true
                }
              }, {
                name: 'endDate',
                type: 'date',
                label: t('end_date'),
                displayed: true,
                options: {
                  time: true
                }
              }, {
                name: 'maxUsers',
                type: 'number',
                label: trans('max_users', {}, 'cursus')
              },  {
                name: 'registrationType',
                type: 'number',
                label: t('registration'),
                render: (rowData) => registrationTypes[rowData.registrationType]
              }, {
                name: 'eventSet',
                type: 'string',
                label: t('group'),
                render: (rowData) => rowData.eventSet ?
                  <a className="pointer-hand" onClick={() => this.showEventSetForm(rowData.eventSet)}>
                    {rowData.eventSet['name']}
                  </a> :
                  ''
              }
            ]}
            actions={(rows) => [
              {
                type: 'callback',
                icon: 'fa fa-fw fa-edit',
                label: t('edit'),
                callback: () => this.showEventEditionForm(rows[0]),
                scope: ['object']
              }, {
                type: 'callback',
                icon: 'fa fa-fw fa-info',
                label: trans('informations_management', {}, 'cursus'),
                callback: () => this.showEventCommentsManagement(rows[0]),
                scope: ['object']
              }, {
                type: 'callback',
                icon: 'fa fa-fw fa-files-o',
                label: trans('repeat_session_event', {}, 'cursus'),
                callback: () => this.showEventRepeatForm(rows[0]),
                scope: ['object']
              }, {
                type: 'callback',
                icon: 'fa fa-fw fa-trash-o',
                label: t('delete'),
                callback: () => this.deleteSessionEvent(rows[0]),
                dangerous: true,
                scope: ['object']
              }
            ]}
            filters={{
              current: this.props.filters,
              addFilter: this.props.addListFilter,
              removeFilter: this.props.removeListFilter
            }}
            sorting={{
              current: this.props.sortBy,
              updateSort: this.props.updateSort
            }}
            pagination={Object.assign({}, this.props.pagination, {
              handlePageChange: this.props.handlePageChange,
              handlePageSizeUpdate: this.props.handlePageSizeUpdate
            })}
            selection={{
              current: this.props.selected,
              toggle: this.props.toggleSelect,
              toggleAll: this.props.toggleSelectAll,
              actions: [{
                label: t('delete'),
                icon: 'fa fa-fw fa-trash-o',
                action: () => this.deleteSessionEvents(this.props.selected),
                dangerous: true
              }]
            }}
          />
          <br/>
          <button className="btn btn-primary" onClick={() => this.showEventCreationForm()}>
            {trans('create_session_event', {}, 'cursus')}
          </button>
        </div>
      )
    } else {
      return (
        <div className="alert alert-warning">
          {trans('no_session_associated_to_workspace', {}, 'cursus')}
        </div>
      )
    }
  }
}

ManagerView.propTypes = {
  workspaceId: T.number.isRequired,
  events: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    startDate: T.string.isRequired,
    endDate: T.string.isRequired,
    registrationType: T.number.isRequired,
    maxUsers: T.number
  })).isRequired,
  session: T.object,
  total: T.number.isRequired,
  createSessionEvent: T.func.isRequired,
  editSessionEvent: T.func.isRequired,
  deleteSessionEvent: T.func.isRequired,
  repeatSessionEvent: T.func.isRequired,
  deleteSessionEvents: T.func.isRequired,
  filters: T.array.isRequired,
  addListFilter: T.func.isRequired,
  removeListFilter: T.func.isRequired,
  sortBy: T.object.isRequired,
  updateSort: T.func.isRequired,
  handlePageSizeUpdate: T.func.isRequired,
  handlePageChange: T.func.isRequired,
  selected: T.array.isRequired,
  toggleSelect: T.func.isRequired,
  toggleSelectAll: T.func.isRequired,
  pagination: T.shape({
    pageSize: T.number.isRequired,
    current: T.number.isRequired
  }).isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    workspaceId: state.workspaceId,
    events: selectors.sessionEvents(state),
    total: selectors.sessionEventsTotal(state),
    session: selectors.currentSession(state),
    selected: listSelect.selected(state),
    filters: listSelect.filters(state),
    sortBy: listSelect.sortBy(state),
    pagination: {
      pageSize: listSelect.pageSize(state),
      current:  listSelect.currentPage(state)
    }
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createSessionEvent: (sessionId, eventData) => {
      dispatch(actions.createSessionEvent(sessionId, eventData))
    },
    editSessionEvent: (eventId, eventData) => {
      dispatch(actions.editSessionEvent(eventId, eventData))
    },
    deleteSessionEvent: (workspaceId, sessionEventId) => {
      dispatch(actions.deleteSessionEvent(workspaceId, sessionEventId))
    },
    repeatSessionEvent: (sessionEventId, repeatEventData) => {
      dispatch(actions.repeatSessionEvent(sessionEventId, repeatEventData))
    },
    deleteSessionEvents: (workspaceId, sessionEvents) => {
      dispatch(actions.deleteSessionEvents(workspaceId, sessionEvents))
    },
    // search
    addListFilter: (property, value) => {
      dispatch(listActions.addFilter(property, value))
      dispatch(actions.fetchSessionEvents())
    },
    removeListFilter: (filter) => {
      dispatch(listActions.removeFilter(filter))
      dispatch(actions.fetchSessionEvents())
    },
    // sorting
    updateSort: (property) => {
      dispatch(listActions.updateSort(property))
      dispatch(actions.fetchSessionEvents())
    },
    // pagination
    handlePageSizeUpdate: (pageSize) => {
      dispatch(listActions.updatePageSize(pageSize))
      dispatch(actions.fetchSessionEvents())
    },
    handlePageChange: (page) => {
      dispatch(listActions.changePage(page))
      dispatch(actions.fetchSessionEvents())
    },
    // selection
    toggleSelect: (id) => dispatch(listActions.toggleSelect(id)),
    toggleSelectAll: (items) => dispatch(listActions.toggleSelectAll(items))
  }
}

const ConnectedManagerView = connect(mapStateToProps, mapDispatchToProps)(withModal(ManagerView))

export {ConnectedManagerView as ManagerView}