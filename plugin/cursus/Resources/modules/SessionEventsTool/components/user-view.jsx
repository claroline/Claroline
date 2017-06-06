import {connect} from 'react-redux'
import React, {Component, PropTypes as T} from 'react'
import {trans, t} from '#/main/core/translation'
import {actions} from '../actions'
import {selectors} from '../selectors'
import {registrationStatus} from '../enums'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'
import {DataList} from '#/main/core/layout/list/components/data-list.jsx'

class UserView extends Component {
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
              {name: 'startDate', type: 'date', label: t('start_date')},
              {name: 'endDate', type: 'date', label: t('end_date')},
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
                  } else if (rowData.registrationType === 2) {
                    return (
                      <button className="btn btn-default" onClick={() => this.props.selfRegisterToSessionEvent(rowData.id)}>
                        {trans('self_register_to_session_event', {}, 'cursus')}
                      </button>
                    )
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
    events: selectors.sessionEvents(state),
    total: selectors.sessionEventsTotal(state),
    session: selectors.currentSession(state),
    eventsUsers: selectors.eventsUsers(state),
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
    selfRegisterToSessionEvent: (sessionEventId) => {
      dispatch(actions.selfRegisterToSessionEvent(sessionEventId))
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
      dispatch(paginationActions.updatePageSize(pageSize))
      dispatch(actions.fetchSessionEvents())
    },
    handlePageChange: (page) => {
      dispatch(paginationActions.changePage(page))
      dispatch(actions.fetchSessionEvents())
    }
  }
}

const ConnectedUserView = connect(mapStateToProps, mapDispatchToProps)(UserView)

export {ConnectedUserView as UserView}