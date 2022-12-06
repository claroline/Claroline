import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
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
          }, {
            name: 'meta.personalWorkspaceCreationEnabled',
            type: 'boolean',
            label: trans('role_personalWorkspaceCreation'),
            help: trans('role_personalWorkspaceCreation_help'),
            displayed: (role) => constants.ROLE_PLATFORM === role.type
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
