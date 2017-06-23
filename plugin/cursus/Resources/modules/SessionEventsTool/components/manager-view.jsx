import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import {trans, t} from '#/main/core/translation'
import {makeModal} from '#/main/core/layout/modal'
import {selectors} from '../selectors'
import {actions} from '../actions'
import {registrationTypes} from '../enums'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'
import {DataList} from '#/main/core/layout/list/components/data-list.jsx'

class ManagerView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      modal: {}
    }
    this.deleteSessionEvent = this.deleteSessionEvent.bind(this)
  }

  deleteSessionEvent(sessionEvent) {
    this.setState({
      modal: {
        type: 'DELETE_MODAL',
        urlModal: null,
        props: {
          url: null,
          isDangerous: true,
          question: trans('delete_session_event_confirm_message', {}, 'cursus'),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            this.props.deleteSessionEvent(this.props.workspaceId, sessionEvent.id)
          },
          title: `${trans('delete_session_event', {}, 'cursus')} [${sessionEvent.name}]`
        },
        fading: false
      }
    })
  }

  deleteSessionEvents(sessionEvents) {
    this.setState({
      modal: {
        type: 'DELETE_MODAL',
        urlModal: null,
        props: {
          url: null,
          isDangerous: true,
          question: trans('delete_selected_session_events_confirm_message', {}, 'cursus'),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            this.props.deleteSessionEvents(this.props.workspaceId, sessionEvents)
          },
          title: `${trans('delete_selected_session_events', {}, 'cursus')}`
        },
        fading: false
      }
    })
  }

  showEventCreationForm() {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_FORM',
        urlModal: null,
        props: {
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
        },
        fading: false
      }
    })
  }

  showEventEditionForm(sessionEvent) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_FORM',
        urlModal: null,
        props: {
          mode: 'edition',
          title: trans('session_event_edition', {}, 'cursus'),
          event: sessionEvent,
          session: this.props.session,
          confirmAction: this.props.editSessionEvent
        },
        fading: false
      }
    })
  }

  showEventRepeatForm(sessionEvent) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_REPEAT_FORM',
        urlModal: null,
        props: {
          title: trans('repeat_session_event', {}, 'cursus'),
          event: sessionEvent,
          repeatSessionEvent: this.props.repeatSessionEvent
        },
        fading: false
      }
    })
  }

  showEventCommentsManagement(sessionEvent) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_COMMENTS',
        urlModal: null,
        props: {
          title:trans('informations_management', {}, 'cursus'),
          event: sessionEvent
        },
        fading: false
      }
    })
  }

  hideModal() {
    this.setState({modal: {fading: true, urlModal: null}})
  }

  showEventSetForm(eventSet) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_SET_FORM',
        urlModal: null,
        props: {
          title: trans('session_event_set_edition', {}, 'cursus'),
          eventSet: eventSet
        },
        fading: false
      }
    })
  }

  render() {
    if (this.props.session) {
      return (
        <div>
          <DataList
            data={this.props.events}
            totalResults={this.props.total}
            definition={[
              {
                name: 'name',
                type: 'string',
                label: t('name'),
                renderer: (rowData) => <a href={`#event/${rowData.id}`}>{rowData.name}</a>
              },
              {
                name: 'startDate',
                type: 'date',
                label: t('start_date'),
                renderer: (rowData) => moment(rowData.startDate).format('DD/MM/YYYY HH:mm')
              },
              {
                name: 'endDate',
                type: 'date',
                label: t('end_date'),
                renderer: (rowData) => moment(rowData.endDate).format('DD/MM/YYYY HH:mm')
              },
              {name: 'maxUsers', type: 'number', label: trans('max_users', {}, 'cursus')},
              {
                name: 'registrationType',
                type: 'number',
                label: t('registration'),
                renderer: (rowData) => registrationTypes[rowData.registrationType]
              },
              {
                name: 'eventSet',
                type: 'string',
                label: t('group'),
                renderer: (rowData) => rowData.eventSet ?
                  <a className="pointer-hand" onClick={() => this.showEventSetForm(rowData.eventSet)}>
                    {rowData.eventSet['name']}
                  </a> :
                  ''
              }
            ]}
            actions={[
              {
                icon: 'fa fa-fw fa-edit',
                label: t('edit'),
                action: (row) => this.showEventEditionForm(row)
              },
              {
                icon: 'fa fa-fw fa-info',
                label: trans('informations_management', {}, 'cursus'),
                action: (row) => this.showEventCommentsManagement(row)
              },
              {
                icon: 'fa fa-fw fa-files-o',
                label: trans('repeat_session_event', {}, 'cursus'),
                action: (row) => this.showEventRepeatForm(row)
              },
              {
                icon: 'fa fa-fw fa-trash-o',
                label: t('delete'),
                action: (row) => this.deleteSessionEvent(row),
                isDangerous: true
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
                isDangerous: true
              }]
            }}
          />
          <br/>
          <button className="btn btn-primary" onClick={() => this.showEventCreationForm()}>
            {trans('create_session_event', {}, 'cursus')}
          </button>
          {this.state.modal.type &&
            this.props.createModal(
              this.state.modal.type,
              this.state.modal.props,
              this.state.modal.fading,
              this.hideModal.bind(this)
            )
          }
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
  createModal: T.func.isRequired,
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
  }).isRequired
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
      pageSize: paginationSelect.pageSize(state),
      current:  paginationSelect.current(state)
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
    createModal: (type, props, fading, hideModal) => makeModal(type, props, fading, hideModal, hideModal),
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
      dispatch(paginationActions.updatePageSize(pageSize))
      dispatch(actions.fetchSessionEvents())
    },
    handlePageChange: (page) => {
      dispatch(paginationActions.changePage(page))
      dispatch(actions.fetchSessionEvents())
    },
    // selection
    toggleSelect: (id) => dispatch(listActions.toggleSelect(id)),
    toggleSelectAll: (items) => dispatch(listActions.toggleSelectAll(items))
  }
}

const ConnectedManagerView = connect(mapStateToProps, mapDispatchToProps)(ManagerView)

export {ConnectedManagerView as ManagerView}