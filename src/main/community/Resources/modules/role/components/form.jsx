import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store'

import {constants} from '#/main/community/constants'

const RoleFormComponent = props =>
  <FormData
    level={3}
    name={props.name}
    buttons={true}
    target={(role, isNew) => isNew ?
      ['apiv2_role_create'] :
      ['apiv2_role_update', {id: role.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'translationKey',
            type: 'translation',
            label: trans('name'),
            required: true,
            disabled: (role) => get(role, 'meta.readOnly')
          }, {
            name: 'type',
            type: 'choice',
            label: trans('type'),
            disabled: (role) => !!role.id, // is new
            required: true,
            options: {
              condensed: true,
              choices: constants.ROLE_TYPES
            },
            onChange: (value) => {
              if (constants.ROLE_WORKSPACE !== value) {
                props.updateProp('workspace', null)
              }

              if (constants.ROLE_USER !== value) {
                props.updateProp('user', null)
              }
            },
            linked: [
              {
                name: 'workspace',
                type: 'workspace',
                label: trans('workspace'),
                required: true,
                disabled: (role) => !!role.id, // is new
                displayed: (role) => constants.ROLE_WORKSPACE === role.type
              }, {
                name: 'user',
                type: 'user',
                label: trans('user'),
                required: true,
                disabled: (role) => !!role.id, // is new
                displayed: (role) => constants.ROLE_USER === role.type
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-circle-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            type: 'string',
            label: trans('description'),
            options: {
              long: true
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-book',
        title: trans('workspace'),
        displayed: (role) => constants.ROLE_PLATFORM === role.type,
        fields: [
          {
            name: 'meta.personalWorkspaceCreationEnabled',
            type: 'boolean',
            label: trans('role_personalWorkspaceCreation'),
            help: trans('role_personalWorkspaceCreation_help')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-tools',
        title: trans('desktop_tools'),
        displayed: (role) => constants.ROLE_PLATFORM === role.type,
        render: (role) => (
          <div className="list-group" fill={true}>
            {Object.keys(role.desktopTools || {}).map(toolName =>
              <div key={toolName} className="tool-rights-row list-group-item">
                <div className="tool-rights-title">
                  {trans(toolName, {}, 'tools')}
                </div>

                <div className="tool-rights-actions">
                  {Object.keys(role.desktopTools[toolName]).map((permName) =>
                    <Checkbox
                      key={permName}
                      id={`${toolName}-${permName}`}
                      label={trans(permName, {}, 'actions')}
                      checked={role.desktopTools[toolName][permName]}
                      onChange={checked => props.updateProp(`desktopTools.${toolName}.${permName}`, checked)}
                    />
                  )}
                </div>
              </div>
            )}
          </div>
        )
      }, {
        icon: 'fa fa-fw fa-cogs',
        title: trans('administration_tools'),
        displayed: (role) => constants.ROLE_PLATFORM === role.type,
        render: (role) => (
          <div className="list-group" fill={true}>
            {Object.keys(role.adminTools || {}).map(toolName =>
              <Checkbox
                key={toolName}
                id={toolName}
                className={classes('list-group-item', {
                  'list-group-item-selected': role.adminTools[toolName]
                })}
                label={trans(toolName, {}, 'tools')}
                checked={role.adminTools[toolName]}
                onChange={checked => props.updateProp(`adminTools.${toolName}`, checked)}
              />
            )}
          </div>
        )
      }
    ].concat(props.customDefinition)}
  >
    {props.children}
  </FormData>

RoleFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  customDefinition: T.array,
  updateProp: T.func.isRequired,
  children: T.any
}

RoleFormComponent.defaultProps = {
  customDefinition: []
}

const RoleForm = connect(
  null,
  (dispatch, ownProps) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(RoleFormComponent)

export {
  RoleForm
}
