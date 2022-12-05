import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store'

const UserFormComponent = props =>
  <FormData
    level={3}
    name={props.name}
    buttons={true}
    target={(user, isNew) => isNew ?
      ['apiv2_user_create'] :
      ['apiv2_user_update', {id: user.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'lastName',
            type: 'string',
            label: trans('last_name'),
            required: true
          }, {
            name: 'firstName',
            type: 'string',
            label: trans('first_name'),
            required: true
          }, {
            name: 'email',
            type: 'email',
            label: trans('email'),
            required: true
          }, {
            name: 'username',
            type: 'username',
            label: trans('username'),
            required: true
          }, {
            name: 'plainPassword',
            type: 'password',
            label: trans('password'),
            displayed: (user) => !user.id, // is new
            required: true,
            options: {
              autoComplete: 'new-password'
            }
          },
          {
            name: 'mainOrganization',
            type: 'organization',
            required: true,
            label: trans('main_organization')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-circle-info',
        title: trans('information'),
        fields: [
          {
            name: 'administrativeCode',
            type: 'string',
            label: trans('administrativeCode')
          }, {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'organizations',
            type: 'organizations',
            label: trans('organizations')
          }, {
            name: 'phone',
            type: 'string',
            label: trans('phone')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'picture',
            type: 'image',
            label: trans('picture')
          }, {
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
            name: 'restrictions.enableDates',
            type: 'boolean',
            label: trans('restrict_by_dates'),
            calculated: (user) => user.restrictions && 0 !== user.restrictions.dates.length,
            onChange: (activated) => {
              if (!activated) {
                props.updateProp('restrictions.dates', [])
              } else {
                props.updateProp('restrictions.dates', [null, null])
              }
            },
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: (user) => user.restrictions && 0!== user.restrictions.dates.length,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }
        ]
      }
    ]}
  />

UserFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  updateProp: T.func.isRequired
}

const UserForm = connect(
  null,
  (dispatch, ownProps) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(UserFormComponent)

export {
  UserForm
}
