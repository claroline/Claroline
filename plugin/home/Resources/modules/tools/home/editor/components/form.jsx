import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const TabForm = props =>
  <FormData
    level={props.level}
    name={props.name}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'longTitle',
            type: 'string',
            label: trans('title'),
            required: true,
            onChange: (title) => props.update('title', title.substring(0, 20))
          }
        ]
      }, {
        icon: 'fa fa-fw fa-grip-horizontal',
        title: trans('menu'),
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            help: trans('menu_title_help'),
            options: {
              maxLength: 20
            },
            onChange: (value) => {
              if (isEmpty(value) && 0 === props.currentTab.icon.length) {
                props.setErrors({title: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas d\'icône'})
              }
            }
          }, {
            name: 'icon',
            type: 'icon',
            label: trans('icon'),
            help: trans('icon_tab_help'),
            onChange: (icon) => {
              if (0 === icon.length && 0 === props.currentTab.title.length) {
                props.setErrors({icon: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas de titre.'})
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.color',
            label: trans('color'),
            type: 'color'
          }, {
            name: 'display.centerTitle',
            type: 'boolean',
            label: trans('center_title')
          }, {
            name: 'poster',
            label: trans('poster'),
            type: 'image',
            options: {
              ratio: '3:1'
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        displayed: (props.administration || 'desktop' !== props.currentContext.type),
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden')
          }, {
            name: 'restrictByRole',
            type: 'boolean',
            label: trans('restrictions_by_roles', {}, 'widget'),
            calculated: (tab) => tab.restrictByRole || !isEmpty(get(tab, 'restrictions.roles')),
            onChange: (checked) => {
              if (!checked) {
                props.update('restrictions.roles', [])
              }
            },
            linked: [
              {
                name: 'restrictions.roles',
                label: trans('roles'),
                displayed: (tab) => tab.restrictByRole || !isEmpty(get(tab, 'restrictions.roles')),
                type: 'roles',
                required: true,
                options: {
                  picker: props.currentContext.type === 'workspace' ? {
                    url: ['apiv2_workspace_list_roles', {id: get(props.currentContext, 'data.id')}],
                    filters: []
                  } : undefined
                }
              }
            ]
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

TabForm.propTypes = {
  level: T.number,
  name: T.string.isRequired,

  currentTab: T.object,
  administration: T.bool.isRequired,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }),

  update: T.func.isRequired,
  setErrors: T.func.isRequired,
  children: T.node
}

export {
  TabForm
}
