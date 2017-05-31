import {connect} from 'react-redux'
import React, {Component, PropTypes as T} from 'react'
import {select} from './selectors'
import {actions} from './actions'
import {LazyLoadTable} from '#/main/core/layout/table/LazyLoadTable.jsx'
import moment from 'moment'
import {makeModal, makeModalFromUrl} from '#/main/core/layout/modal'
import Configuration from '#/main/core/library/Configuration/Configuration'
import classes from 'classnames'
import ReactDOM from 'react-dom'

/* global Translator */
/* global Routing */

const t = key => Translator.trans(key, {}, 'platform')
const route = (name, parameter = {}) => Routing.generate(name, parameter)

class ActionCell extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <ul className="btn-group menu user-actions-menu">
          <li className="btn btn-default workspace-additional-action" onClick={() => this.props.removeWorkspaces([this.props.workspace])}>
            <i className="fa fa-trash workspace-action"/>
          </li>

          {Configuration.getWorkspacesAdministrationActions().map(button => {
            return(
              <li className="btn btn-default workspace-additional-action">
                <i
                  className={classes(button.class, 'workspace-action')}
                  data-url={button.url(this.props.workspace.id)}
                  data-toggle="tooltip"
                  data-placement="left"
                  title={button.name()}
                  data-display-mode="modal_form"
                />
              </li>
            )
          })}
      </ul>
    )
  }
}

ActionCell.propTypes = {
  workspace: T.object.isRequired,
  removeWorkspaces: T.func.isRequired
}

const NameCell = el => {
  return <span> <a href={route('claro_workspace_open', {workspaceId: el.id})} > {el.name} </a> </span>
}

const DateCell = el => {
  return (<span> {moment(el.createDate).format(t('date_range.js_format'))} </span>)
}

class Workspaces extends Component {
  constructor(props) {
    super(props)

    this.columns = {
      available: ['name', 'code', 'creationDate', 'actions'],
      active: []
    }

    this.filters = {
      available: ['name', 'code'],
      active: [],
      onChange: () => alert('filter')
    }

    this.renderers = {
      actions: el => <ActionCell workspace={el} removeWorkspaces={this.removeWorkspaces.bind(this)}/>,
      creationDate: el => DateCell(el),
      name: el => NameCell(el)
    }

    this.state = {
      modal: {}
    }
  }

  removeWorkspaces(workspaces) {
    this.setState({
      modal: {
        type: 'DELETE_MODAL',
        urlModal: null,
        props: {
          url: null,
          isDangerous: true,
          question: t('remove_workspaces_confirm', {workspace_list: workspaces.reduce((acc, workspace) => workspace.name + ' ,')}),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            return this.props.removeWorkspaces(workspaces)
          },
          title: t('remove_workspace')
        },
        fading: false
      }
    })
  }

  copySelection() {
    this.setState({
      modal: {
        urlModal: null,
        type: 'CONFIRM_MODAL',
        props: {
          url: null,
          isDangerous: false,
          question: t('copy_workspaces_confirm', {workspace_list: this.props.pagination.selected.reduce((acc, workspace) => workspace.name + ' ,')}),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            return this.props.copyWorkspaces(this.props.pagination.selected, 0)
          },
          title: t('copy_workspace')
        },
        fading: false
      }
    })
  }

  copyAsModelSelection() {
    this.setState({
      modal: {
        type: 'CONFIRM_MODAL',
        url: null,
        props: {
          isDangerous: false,
          question: t('copy_model_workspaces_confirm', {workspace_list: this.props.pagination.selected.reduce((acc, workspace) => workspace.name + ' ,')}),
          handleConfirm: () =>  {
            this.setState({modal: {fading: true}})

            return this.props.copyWorkspaces(this.props.pagination.selected, 1)
          },
          title: t('copy_model_workspace')
        },
        fading: false
      }
    })
  }


  hideModal() {
    ReactDOM.unmountComponentAtNode(document.getElementById('url-modal'))
    this.setState({modal: {fading: true, urlModal: null}})
  }

  componentDidMount() {
    const els = document.getElementsByClassName('workspace-additional-action')
    const array = []
    //because it's an arrayNode collection or something, we can't use forEach directtly
    array.forEach.call(els, el => {
      el.addEventListener(
        'click',
        event => {
          const node = event.target.querySelector('.workspace-action') || event.target
          const url = node.dataset.url
          const mode = node.dataset.displayMode

          if (mode === 'modal_form') {
            this.setState({
              modal: {
                type: 'URL_MODAL',
                fading: false,
                url
              }
            })
            {this.state.modal.type === 'URL_MODAL' &&
              this.props.createModalFromUrl(
                this.state.modal.fading,
                this.hideModal.bind(this),
                this.state.modal.url
              ).then(data => {
                this.setState({modal: { urlModal: data} })
                ReactDOM.render(data, document.getElementById('url-modal'))
              })
            }
          } else {
            window.location = url
          }
        }
      )
    })
  }


  render() {
    return (
      <div className="panel panel-body">
        <div>
          {this.state.modal.type && this.state.modal.type !== 'URL_MODAL' &&
            this.props.createModal(
              this.state.modal.type,
              this.state.modal.props,
              this.state.modal.fading,
              this.hideModal.bind(this)
            )
          }
          <div id='url-modal'></div>
          <span className="table-actions">
            <button className="btn btn-default action-button" onClick={() => this.removeWorkspaces(this.props.pagination.selected)}> {t('delete')} </button>
            <button className="btn btn-default action-button" onClick={() => this.copySelection()}> {t('copy')} </button>
            <button className="btn btn-default action-button" onClick={() => this.copyAsModelSelection()}> {t('make_model')} </button>
          </span>
          <span className="generic-actions">
            <a href={route('claro_workspace_creation_form')} className="btn btn-default action-button" role="button">
                <i className="fa fa-pencil"></i>
                {t('create')}
            </a>
            <a href={route('claro_admin_workspace_import_form')} className="btn btn-default action-button">
                <i className="icon-book"></i>
                {t('import_csv', 'platform')}
            </a>
          </span>
        </div>
        <LazyLoadTable
          format="list"
          columns={this.columns}
          filters={this.filters}
          pagination={this.props.pagination}
          renderers={this.renderers}
          onChangePage={this.props.onChangePage}
          onSelect={this.props.onSelect}
        />
      </div>
    )
  }
}

Workspaces.propTypes = {
  pagination: {
    totalResults: T.number.required,
    pageSize: T.number.required,
    current: T.number.required,
    data: T.array(T.object)
  },
  onChangePage: T.func.isRequired,
  onSelect: T.func.isRequired,
  createModal: T.func.isRequired,
  createModalFromUrl: T.func.isRequired,
  removeWorkspaces: T.func.isRequired,
  copyWorkspaces: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    pagination: {
      data: select.data(state),
      totalResults: select.totalResults(state),
      pageSize: select.pageSize(state),
      current: select.current(state),
      selected: select.selected(state)
    }
  }
}

function mapDispatchToProps(dispatch) {
  return {
    onChangePage: (page, size) => dispatch(actions.fetchPage(page, size)),
    onSelect: (selected) => dispatch(actions.onSelect(selected)),
    createModal: (type, props, fading, hideModal) => makeModal(type, props, fading, hideModal, hideModal),
    createModalFromUrl: (fading, hideModal, url) => makeModalFromUrl(fading, hideModal, url),
    removeWorkspaces: (workspaces) => dispatch(actions.removeWorkspaces(workspaces)),
    copyWorkspaces: (workspaces, isModel) => dispatch(actions.copyWorkspaces(workspaces, isModel))
  }
}

const ConnectedWorkspaces = connect(mapStateToProps, mapDispatchToProps)(Workspaces)

export {ConnectedWorkspaces as Workspaces}
