import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {withRouter} from '#/main/app/router'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'

import {actions} from '#/main/core/workspace/creation/store/actions'
import {actions as logActions} from '#/main/core/workspace/creation/components/log/actions'
import {Logs} from '#/main/core/workspace/creation/components/log/components/logs'


class WorkspaceComponent extends Component
{
  constructor(props) {
    super(props)

    this.state = {
      refresh: false
    }
  }

  refreshLog() {
    const props = this.props

    if (this.state.refresh) {
      if (!props.logData.end) {
        let loader = setInterval(() => {

          clearInterval(loader)

          props.loadLog(props.workspace.code)
        }, 1500)
      }
    }
  }

  componentDidUpdate()
  {
    this.refreshLog()
  }

  render() {
    const props = this.props
    const modelChoices = {}
    let defaultModel = null

    props.models.data.forEach(model => {
      modelChoices[model.uuid] = model.code
      if (model.code === 'default_workspace') {
        defaultModel = model.code
      }
    })

    return (
      <FormData
        level={3}
        name="workspaces.current"
        buttons={true}
        save={{
          type: 'callback',
          callback: () => {
            props.save(props.workspace, props.history)
            this.refreshLog()
            this.setState({refresh: true})
          }
        }}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'name',
                type: 'string',
                label: trans('name'),
                required: true
              }, {
                name: 'code',
                type: 'string',
                label: trans('code'),
                required: true
              }, {
                name: 'model',
                type: 'choice',
                label: trans('model'),
                required: true,
                options: {
                  condensed: true,
                  choices: modelChoices
                },
                value: defaultModel
              }
            ]
          }, {
            icon: 'fa fa-fw fa-info',
            title: trans('information'),
            fields: [
              {
                name: 'meta.description',
                type: 'string',
                label: trans('description'),
                options: {
                  long: true
                }
              }, {
                name: 'meta.model',
                label: trans('model'),
                type: 'boolean',
                disabled: false
              }, {
                name: 'meta.forceLang',
                type: 'boolean',
                label: trans('default_language'),
                onChange: activated => {
                  if (!activated) {
                  // reset lang field
                    props.updateProp('meta.lang', null)
                  }
                },
                linked: [{
                  name: 'meta.lang',
                  label: trans('lang'),
                  type: 'locale',
                  displayed: (workspace) => workspace.meta.forceLang
                }]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-sign-in',
            title: trans('opening_parameters'),
            fields: [
              {
                name: 'opening.type',

                type: 'choice',
                label: trans('type'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: {
                    tool: trans('open_workspace_tool'),
                    resource: trans('open_workspace_resource')
                  }
                },
                onChange: () => {
                  props.updateProp('opening.target', null)
                },
                linked: [
                  {
                    name: 'opening.target',
                    type: 'string',
                    label: trans('tool'),
                    required: true,
                    displayed: (workspace) => workspace.opening && 'tool' === workspace.opening.type
                  }, {
                    name: 'opening.target',
                    type: 'string',
                    label: trans('resource'),
                    help: trans('opening_target_resource_help'),
                    required: true,
                    displayed: (workspace) => workspace.opening && 'resource' === workspace.opening.type
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'thumbnail',
                type: 'image',
                label: trans('thumbnail')
              }, {
                name: 'poster',
                type: 'image',
                label: trans('poster')
              }, {
                name: 'display.color',
                type: 'color',
                label: trans('color')
              }, {
                name: 'display.showBreadcrumbs',
                type: 'boolean',
                label: trans('showBreadcrumbs')
              }, {
                name: 'display.showTools'
                ,
                type: 'boolean',
                label: trans('showTools')
              }
            ]
          }, {
            icon: 'fa fa-fw fa-user-plus',
            title: trans('registration'),
            fields: [ {
              name: 'registration.selfRegistration',
              type: 'boolean',
              label: trans('activate_self_registration'),
              help: trans('self_registration_workspace_help'),
              linked: [
                {
                  name: 'registration.validation',
                  type: 'boolean',
                  label: trans('validate_registration'),
                  help: trans('validate_registration_help'),
                  displayed: (workspace) => workspace.registration && workspace.registration.selfRegistration
                }
              ]
            }, {
              name: 'registration.selfUnregistration',
              type: 'boolean',
              label: trans('activate_self_unregistration'),
              help: trans('self_unregistration_workspace_help')
            }
            ]
          }, {
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('restrict_hidden'),
                help: trans('restrict_hidden_help')
              }, {
                name: 'restrictions.enableDates',
                label: trans('restrict_by_dates'),
                type: 'boolean',
                onChange: activated => {
                  if (!activated) {
                    props.updateProp('restrictions.dates', [])
                  }
                },
                linked: [
                  {
                    name: 'restrictions.dates',
                    type: 'date-range',
                    label: trans('access_dates'),
                    required: true,
                    options: {
                      time: true
                    }
                  }
                ]
              }, {
                name: 'restrictions.enableMaxUsers',
                type: 'boolean',
                label: trans('restrict_max_users'),
                onChange: activated => {
                  if (!activated) {
                  // reset max users field
                    props.updateProp('restrictions.maxUsers', null)
                  }
                },
                linked: [
                  {
                    name: 'restrictions.maxUsers',
                    type: 'number',
                    label: trans('maxUsers'),
                    required: true,
                    options: {
                      min: 0
                    }
                  }
                ]
              }, {
                name: 'restrictions.enableMaxResources',
                type: 'boolean',
                label: trans('restrict_max_resources'),
                onChange: activated => {
                  if (!activated) {
                  // reset max users field
                    props.updateProp('restrictions.maxResources', null)
                  }
                },
                linked: [
                  {
                    name: 'restrictions.maxResources',
                    type: 'number',
                    label: trans('max_amount_resources'),
                    required: true,
                    options: {
                      min: 0
                    }
                  }
                ]
              }, {
                name: 'restrictions.enableMaxStorage',
                type: 'boolean',
                label: trans('restrict_max_storage'),
                onChange: activated => {
                  if (!activated) {
                  // reset max users field
                    props.updateProp('restrictions.maxStorage', null)
                  }
                },
                linked: [
                  {
                    name: 'restrictions.maxStorage',
                    type: 'storage',
                    label: trans('max_storage_size'),
                    displayed: true,
                    required: true,
                    options: {
                      min: 0
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-bell-o',
            title: trans('notifications'),
            fields: [
              {
                name: 'notifications.enabled',
                type: 'boolean',
                label: trans('enable_notifications')
              }
            ]
          }
        ]}
      >
        <Logs/>
      </FormData>
    )}

}

WorkspaceComponent.propTypes = {
  loadLog: T.func,
  history: T.object,
  updateProp: T.func,
  save: T.func,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  models: T.array.isRequired,
  logData: T.object
}

WorkspaceComponent.defaultProps = {
  workspace: WorkspaceTypes.defaultProps
}

const ConnectedForm = withRouter(connect(
  state => {
    return {
      models: state.models,
      workspace: formSelect.data(formSelect.form(state, 'workspaces.current')),
      logData: state.workspaces.creation.log //always {} for some reason
      //logData: state
    }
  },
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    },
    loadLog(filename) {
      dispatch(logActions.load(filename))
    },
    save(workspace, history) {
      dispatch(actions.save(workspace, history))
    }
  })
)(WorkspaceComponent))

export {
  ConnectedForm as WorkspaceForm
}
