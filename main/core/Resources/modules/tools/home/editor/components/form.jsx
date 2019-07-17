import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {WidgetGridEditor} from '#/main/core/widget/editor/components/grid'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

import {selectors} from '#/main/core/tools/home/editor/store/selectors'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

const EditorForm = props =>
  <FormData
    name={selectors.FORM_NAME}
    dataPart={`[${props.currentTabIndex}]`}
    buttons={true}
    lock={{
      id: props.currentTab ? props.currentTab.id: null,
      className:'Claroline\\CoreBundle\\Entity\\Tab\\HomeTab'
    }}
    disabled={props.readOnly}
    target={props.administration ?
      ['apiv2_home_admin', {
        context: props.currentContext.type,
        contextId: props.currentContext.data ? props.currentContext.data.uuid : get(props.currentUser, 'id')
      }] :
      ['apiv2_home_update', {
        context: props.currentContext.type,
        contextId: props.currentContext.data ? props.currentContext.data.uuid : get(props.currentUser, 'id')
      }]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/tab/${props.currentTab.id}`,
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-plus',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'longTitle',
            type: 'string',
            label: trans('title'),
            required: true,
            onChange: (title) => props.update(props.currentTabIndex, 'title', title.substring(0, 20))
          }
        ]
      }, {
        icon: 'fa fa-fw fa-grip-horizontal',
        title: trans('menu'),
        fields: [
          {
            name: 'position',
            type: 'number',
            label: trans('position'),
            options : {
              min: 0,
              max: props.tabs.length + 1
            },
            required: true,
            onChange: (newPosition) => props.move(props.tabs, props.currentTab, newPosition)
          }, {
            name: 'title',
            type: 'string',
            label: trans('title'),
            help: trans('menu_title_help'),
            options: {
              maxLength: 20
            },
            onChange: (value) => {
              if (isEmpty(value) && 0 === props.currentTab.icon.length) {
                props.setErrors({
                  [props.currentTabIndex]: {title: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas d\'icône'}
                })
              }
            }
          }, {
            name: 'icon',
            type: 'string',
            label: trans('icon'),
            help: trans('icon_tab_help'),
            onChange: (icon) => {
              if (0 === icon.length && 0 === props.currentTab.title.length) {
                props.setErrors({
                  [props.currentTabIndex]: {icon: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas de titre.'}
                })
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
            name: 'centerTitle',
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
        displayed: props.currentContext.type === 'workspace' || props.administration,
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden')
          }, {
            name: 'restrictByRole',
            type: 'boolean',
            label: trans('restrictions_by_roles', {}, 'widget'),
            calculated: (tab) => tab.restrictByRole || (tab.restrictions && !isEmpty(tab.restrictions.roles)),
            onChange: (checked) => {
              if (!checked) {
                props.update(props.currentTabIndex, 'restrictions.roles', [])
              }
            },
            linked: [
              {
                name: 'restrictions.roles',
                label: trans('role'),
                displayed: (tab) => tab.restrictByRole || (tab.restrictions && !isEmpty(tab.restrictions.roles)),
                type: 'choice',
                required: true,
                options:{
                  inline: false,
                  multiple : true,
                  choices: props.currentContext.type === 'workspace' || props.administration ?
                    props.currentContext.data.roles.reduce((acc, role) => {
                      acc[role.id] = trans(role.translationKey)
                      return acc
                    }, {})
                    : ''
                }
              }
            ]
          }
        ]
      }
    ]}
  >
    <WidgetGridEditor
      disabled={props.readOnly}
      currentContext={props.currentContext}
      widgets={props.widgets}
      tabs={props.tabs}
      currentTabIndex={props.currentTabIndex}
      update={(widgets, tabIndex = null) => {
        if (tabIndex === null) tabIndex = props.currentTabIndex
        props.update(tabIndex, 'widgets', widgets)}
      }
    />
  </FormData>

EditorForm.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  currentContext: T.object.isRequired,
  currentTab: T.shape(TabTypes.propTypes),

  administration: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )).isRequired,

  created: T.bool,
  readOnly: T.bool,
  currentTabIndex: T.number.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,
  update: T.func.isRequired,
  setErrors: T.func.isRequired,
  move: T.func.isRequired
}

export {
  EditorForm
}
