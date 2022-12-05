import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as formActions} from '#/main/app/content/form/store'

import {constants} from '#/main/community/organization/constants'

const OrganizationFormComponent = props =>
  <FormData
    level={3}
    name={props.name}
    buttons={true}
    target={(organization, isNew) => isNew ?
      ['apiv2_organization_create'] :
      ['apiv2_organization_update', {id: organization.id}]
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
            name: 'parent',
            type: 'organization',
            label: trans('parent')
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
          }, {
            name: 'type',
            type: 'choice',
            label: trans('type'),
            options: {
              choices: constants.ORGANIZATION_TYPES
            }
          }, {
            name: 'email',
            type: 'email',
            label: trans('email')
          }, {
            name: 'vat',
            label: trans('vat_number'),
            type: 'string',
            required: false
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.public',
            type: 'boolean',
            label: trans('make_organization_public', {}, 'community'),
            help: [
              trans('make_organization_public_help1', {}, 'community'),
              trans('make_organization_public_help2', {}, 'community')
            ]
          }, {
            name: 'restrictions.maxUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (organization) => get(organization, 'restrictions.maxUsers') || get(organization, 'restrictions.users', -1) > -1,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('restrictions.users', -1)
              } else {
                props.updateProp('restrictions.users', null)
              }
            },
            linked: [
              {
                name: 'restrictions.users',
                type: 'number',
                label: trans('users_count'),
                displayed: (organization) => get(organization, 'restrictions.maxUsers') || get(organization, 'restrictions.users', -1) > -1
              }
            ]
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

OrganizationFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  updateProp: T.func.isRequired,
  children: T.any
}

const OrganizationForm = connect(
  null,
  (dispatch, ownProps) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(OrganizationFormComponent)

export {
  OrganizationForm
}
