import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import {t} from '#/main/core/translation'
import {makeModal} from '#/main/core/layout/modal'
import {selectors} from '../selectors'
import {actions} from '../actions'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'
import {DataList} from '#/main/core/layout/list/components/data-list.jsx'
import {navigate} from '../router'

class ManagementView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      modal: {}
    }
    this.deleteTask = this.deleteTask.bind(this)
  }

  deleteTask(task) {
    this.setState({
      modal: {
        type: 'DELETE_MODAL',
        urlModal: null,
        props: {
          url: null,
          isDangerous: true,
          question: t('delete_scheduled_task'),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            //this.props.deleteTask(task.id)
          },
          title: `${('delete_task')} [${task.name}]`
        },
        fading: false
      }
    })
  }

  deleteTasks(tasks, multiple = true) {
    const question = multiple ? t('delete_selected_tasks_confirm_message') : t('delete_task_confirm_message')
    const title = multiple ? t('delete_selected_tasks') : t('delete_task')

    this.setState({
      modal: {
        type: 'DELETE_MODAL',
        urlModal: null,
        props: {
          url: null,
          isDangerous: true,
          question: question,
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            this.props.deleteTasks(tasks)
          },
          title: title
        },
        fading: false
      }
    })
  }

  showTaskEditionForm(task) {
    this.props.loadTaskForm(task)
    navigate(task.type, true)
  }

  showTaskTypeForm() {
    this.setState({
      modal: {
        type: 'MODAL_TASK_TYPE_FORM',
        urlModal: null,
        props: {
          title: t('task_type_selection_title')
        },
        fading: false
      }
    })
  }

  showTaskDetails(task) {
    const type = task.type === 'mail' ? 'MESSAGE' : task.type.toUpperCase()

    this.setState({
      modal: {
        type: `MODAL_DETAILS_TASK_${type}`,
        urlModal: null,
        props: {
          title: task.name ? task.name : t(task.type),
          task: task
        },
        fading: false
      }
    })
  }

  hideModal() {
    this.setState({modal: {fading: true, urlModal: null}})
  }

  render() {
    if (this.props.isCronConfigured) {
      return (
        <div>
          <DataList
            data={this.props.tasks}
            totalResults={this.props.total}
            definition={[
              {
                name: 'name',
                type: 'string',
                label: t('title'),
                renderer: (rowData) =>
                  <a className="pointer-hand" onClick={() => this.showTaskDetails(rowData)}>
                    {rowData.name}
                  </a>
              },
              {
                name: 'type',
                type: 'string',
                label: t('type'),
                renderer: (rowData) => t(rowData.type)
              },
              {
                name: 'scheduledDate',
                type: 'date',
                label: t('scheduled_date'),
                renderer: (rowData) => moment(rowData.scheduledDate).format('DD/MM/YYYY HH:mm')
              },
              {
                name: 'executed',
                type: 'boolean',
                label: t('executed')
              }
            ]}
            actions={[
              {
                icon: 'fa fa-fw fa-eye',
                label: t('view'),
                action: (row) => this.showTaskDetails(row)
              },
              {
                icon: 'fa fa-fw fa-edit',
                label: t('edit'),
                action: (row) => this.showTaskEditionForm(row)
              },
              {
                icon: 'fa fa-fw fa-trash-o',
                label: t('delete'),
                action: (row) => this.deleteTasks([row.id], false),
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
                action: () => this.deleteTasks(this.props.selected),
                isDangerous: true
              }]
            }}
          />
          <br/>
          <button className="btn btn-primary" onClick={() => this.showTaskTypeForm()}>
            {t('add_a_task')}
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
        <div className="alert alert-danger">
          {t('cron_not_configured_msg')}
        </div>
      )
    }
  }
}

ManagementView.propTypes = {
  isCronConfigured: T.bool.isRequired,
  tasks: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string,
    scheduledDate: T.string.isRequired
  })).isRequired,
  total: T.number.isRequired,
  loadTaskForm: T.func,
  deleteTasks: T.func,
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
    isCronConfigured: selectors.isCronConfigured(state),
    tasks: state.tasks.data,
    total: state.tasks.total,
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
    loadTaskForm: task => dispatch(actions.loadTaskForm(task)),
    deleteTasks: tasks => dispatch(actions.deleteTasks(tasks)),
    createModal: (type, props, fading, hideModal) => makeModal(type, props, fading, hideModal, hideModal),
    // search
    addListFilter: (property, value) => {
      dispatch(listActions.addFilter(property, value))
      dispatch(actions.fetchTasks())
    },
    removeListFilter: (filter) => {
      dispatch(listActions.removeFilter(filter))
      dispatch(actions.fetchTasks())
    },
    // sorting
    updateSort: (property) => {
      dispatch(listActions.updateSort(property))
      dispatch(actions.fetchTasks())
    },
    // pagination
    handlePageSizeUpdate: (pageSize) => {
      dispatch(paginationActions.updatePageSize(pageSize))
      dispatch(actions.fetchTasks())
    },
    handlePageChange: (page) => {
      dispatch(paginationActions.changePage(page))
      dispatch(actions.fetchTasks())
    },
    // selection
    toggleSelect: (id) => dispatch(listActions.toggleSelect(id)),
    toggleSelectAll: (items) => dispatch(listActions.toggleSelectAll(items))
  }
}

const ConnectedManagementView = connect(mapStateToProps, mapDispatchToProps)(ManagementView)

export {ConnectedManagementView as ManagementView}