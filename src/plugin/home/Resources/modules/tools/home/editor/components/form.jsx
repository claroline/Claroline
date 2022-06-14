import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const restrictedByDates = (tab) => get(tab, 'restrictions.enableDates') || !isEmpty(get(tab, 'restrictions.dates'))
const restrictedByCode = (tab) => get(tab, 'restrictions.enableCode') || !!get(tab, 'restrictions.code')
const restrictedByRoles = (tab) => get(tab, 'restrictions.enableRoles') || !isEmpty(get(tab, 'restrictions.roles'))

const TabForm = (props) =>
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
              maxLength: 64
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
            name: 'display.showTitle',
            label: trans('show_title'),
            type: 'boolean',
            linked: [
              {
                name: 'display.centerTitle',
                type: 'boolean',
                label: trans('center_title'),
                displayed: (homeTab) => get(homeTab, 'display.showTitle', false)
              }
            ]
          }, {
            name: 'poster',
            label: trans('poster'),
            type: 'image'
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
            name: 'restrictions.enableDates',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            calculated: restrictedByDates,
            onChange: activated => {
              if (!activated) {
                props.update('restrictions.dates', [])
              }
            },
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: restrictedByDates,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }, {
            name: 'restrictions.enableCode',
            label: trans('restrict_by_code'),
            type: 'boolean',
            calculated: restrictedByCode,
            onChange: activated => {
              if (!activated) {
                props.update('restrictions.code', '')
              }
            },
            linked: [
              {
                name: 'restrictions.code',
                label: trans('access_code'),
                displayed: restrictedByCode,
                type: 'password',
                required: true
              }
            ]
          }, {
            name: 'restrictions.enableRoles',
            type: 'boolean',
            label: trans('restrictions_by_roles', {}, 'widget'),
            calculated: restrictedByRoles,
            onChange: (checked) => {
              if (!checked) {
                props.update('restrictions.roles', [])
              }
            },
            linked: [
              {
                name: 'restrictions.roles',
                label: trans('roles'),
                displayed: restrictedByRoles,
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
