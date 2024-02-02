import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/community/role/routing'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {constants} from '#/main/community/constants'

const RoleFormComponent = props => {
  const history = useHistory()

  return (
    <FormData
      className={props.className}
      level={3}
      name={props.name}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.save(props.role, props.isNew, props.name).then(role => {
          history.push(route(role))
        })
      }}
      cancel={{
        type: LINK_BUTTON,
        target: props.isNew ? props.path + '/roles' : route(props.role, props.path),
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
  )
}

RoleFormComponent.propTypes = {
  className: T.string,
  path: T.string.isRequired,
  name: T.string.isRequired,
  isNew: T.bool.isRequired,
  role: T.object.isRequired,
  customDefinition: T.array,
  save: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.any
}

RoleFormComponent.defaultProps = {
  customDefinition: []
}

const RoleForm = connect(
  (state, ownProps) =>({
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    role: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    save(role, isNew, name) {
      return dispatch( formActions.saveForm(name, isNew ?
        ['apiv2_role_create'] :
        ['apiv2_role_update', {id: role.id}])
      )
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(RoleFormComponent)

export {
  RoleForm
}
