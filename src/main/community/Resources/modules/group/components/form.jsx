import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/community/group/routing'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

const GroupFormComponent = props => {
  const history = useHistory()

  return (
    <FormData
      level={3}
      name={props.name}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.save(props.group, props.isNew, props.name).then(group => {
          history.push(route(group))
        })
      }}
      cancel={{
        type: LINK_BUTTON,
        target: props.isNew ? props.path : route(props.group),
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
              required: true,
              disabled: (group) => get(group, 'meta.readOnly')
            }, {
              name: 'code',
              type: 'string',
              required: true,
              label: trans('code'),
              options: {
                unique: {
                  check: ['apiv2_group_get', {field: 'code'}]
                }
              }
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
              name: 'organizations',
              type: 'organizations',
              label: trans('organizations')
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
        }
      ]}
    >
      {props.children}
    </FormData>
  )
}

GroupFormComponent.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  isNew: T.bool.isRequired,
  group: T.object.isRequired,
  save: T.func.isRequired,
  children: T.any
}

const GroupForm = connect(
  (state, ownProps) => ({
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    group: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch) => ({
    save(group, isNew, name) {
      return dispatch(formActions.saveForm(name, isNew ?
        ['apiv2_group_create'] :
        ['apiv2_group_update', {id: group.id}])
      )
    }
  })
)(GroupFormComponent)

export {
  GroupForm
}
