import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import {trans, t} from '#/main/core/translation'
import {makeModal} from '#/main/core/layout/modal'
import {actions} from '../actions'
import {selectors} from '../selectors'
import {registrationStatus} from '../enums'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {DataList} from '#/main/core/layout/list/components/data-list.jsx'

class UserView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      modal: {}
    }
  }

  showSessionEventSet(eventSet) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_SET_REGISTRATION',
        urlModal: null,
        props: {
          title:trans('session_event_set_registration', {}, 'cursus'),
          eventSet: eventSet
        },
        fading: false
      }
    })
  }

  hideModal() {
    this.setState({modal: {fading: true, urlModal: null}})
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
              {
                name: 'registration',
                type: 'none',
                label: t('registration'),
                renderer: (rowData) => {
                  if (this.props.eventsUsers[rowData.id]) {
                    switch (this.props.eventsUsers[rowData.id].registrationStatus) {
                      case 0 :
                        return (
                          <label className="label label-success">
                            {registrationStatus[this.props.eventsUsers[rowData.id].registrationStatus]}
                          </label>
                        )
                      case 1 :
                        return (
                          <label className="label label-warning">
                            {registrationStatus[this.props.eventsUsers[rowData.id].registrationStatus]}
                          </label>
                        )
                      default :
                        return ('')
                    }
                  } else if (!this.props.disableRegistration && rowData.registrationType === 2) {
                    if (rowData.eventSet) {
                      return (
                        <button className="btn btn-default" onClick={() => this.showSessionEventSet(rowData.eventSet)}>
                          <span className="label label-info">
                            {rowData.eventSet['name']}
                          </span>
                          &nbsp;
                          {trans('self_register_to_session_event_set', {}, 'cursus')}
                        </button>
                      )
                    } else {
                      return (
                        <button className="btn btn-default" onClick={() => this.props.selfRegisterToSessionEvent(rowData.id)}>
                          {trans('self_register_to_session_event', {}, 'cursus')}
                        </button>
                      )
                    }
                  } else {
                    return ('')
                  }
                }
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
          />
          {this.state.modal.type && this.props.createModal(
            this.state.modal.type,
            this.state.modal.props,
            this.state.modal.fading,
            this.hideModal.bind(this)
          )}
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

UserView.propTypes = {
  disableRegistration: T.bool,
  events: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    startDate: T.string.isRequired,
    endDate: T.string.isRequired,
    registrationType: T.number.isRequired
  })).isRequired,
  session: T.object,
  total: T.number.isRequired,
  eventsUsers: T.object,
  selfRegisterToSessionEvent: T.func,
  createModal: T.func.isRequired,
  filters: T.array.isRequired,
  addListFilter: T.func.isRequired,
  removeListFilter: T.func.isRequired,
  sortBy: T.object.isRequired,
  updateSort: T.func.isRequired,
  handlePageSizeUpdate: T.func.isRequired,
  handlePageChange: T.func.isRequired,
  pagination: T.shape({
    pageSize: T.number.isRequired,
    current: T.number.isRequired
  }).isRequired
}

function mapStateToProps(state) {
  return {
    disableRegistration: selectors.disableRegistration(state),
    events: selectors.sessionEvents(state),
    total: selectors.sessionEventsTotal(state),
    session: selectors.currentSession(state),
    eventsUsers: selectors.eventsUsers(state),
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
    selfRegisterToSessionEvent: (sessionEventId) => {
      dispatch(actions.selfRegisterToSessionEvent(sessionEventId))
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
      dispatch(listActions.updatePageSize(pageSize))
      dispatch(actions.fetchSessionEvents())
    },
    handlePageChange: (page) => {
      dispatch(listActions.changePage(page))
      dispatch(actions.fetchSessionEvents())
    }
  }
}

const ConnectedUserView = connect(mapStateToProps, mapDispatchToProps)(UserView)

export {ConnectedUserView as UserView}