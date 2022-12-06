import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/community/tools/community/store'

const UserForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.users.current`}
    buttons={true}
    target={(user, isNew) => isNew ?
      ['apiv2_user_create'] :
      ['apiv2_user_update', {id: user.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/users',
      exact: true
    }}
    definition={[
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
            displayed: props.new,
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'meta.locale',
            type: 'locale',
            label: trans('language'),
            required: true,
            options: {
              onlyEnabled: true
            }
          }, {
            name: 'picture',
            type: 'image',
            label: trans('picture')
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
            onChange: activated => {
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
                displayed: props.user.restrictions && 0!== props.user.restrictions.dates.length,
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

UserForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  user: T.shape({
    id: T.string,
    restrictions: T.shape({
      dates: T.arrayOf(T.string).isRequired
    })
  }).isRequired,
  updateProp: T.func.isRequired
}

const User = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.users.current')),
    user: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.users.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(baseSelectors.STORE_NAME+'.users.current', propName, propValue))
    }
  })
)(UserForm)

export {
  User
}
