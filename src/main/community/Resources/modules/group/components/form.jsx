import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

const GroupForm = props =>
  <FormData
    level={3}
    name={props.name}
    buttons={true}
    target={(group, isNew) => isNew ?
      ['apiv2_group_create'] :
      ['apiv2_group_update', {id: group.id}]
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
            required: true,
            disabled: (group) => get(group, 'meta.readOnly')
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

GroupForm.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  children: T.any
}

export {
  GroupForm
}
