import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {selectors} from '#/main/core/tools/home/editor/selectors'

const TabFormComponent = props =>
  <FormContainer
    level={props.level}
    name={props.name}
    sections={
      [
        {
          icon: 'fa fa-fw fa-plus',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('menu_title'),
              help: trans('menu_title_help'),
              options: {
                maxLength: 20
              },
              required: true
            }, {
              name: 'longTitle',
              type: 'string',
              label: trans('title'),
              required: true,
              linked :[
                {
                  name: 'centerTitle',
                  type: 'boolean',
                  label: trans('center_title')
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'position',
              type: 'number',
              label: trans('position'),
              options : {
                min : 1,
                max : props.editorTabs.length + 1
              }
            },
            {
              name: 'icon',
              type: 'string',
              label: trans('icon'),
              help: trans('icon_tab_help')
            }, {
              name: 'poster',
              label: trans('poster'),
              type: 'file',
              options: {
                ratio: '3:1'
              }
            }
          ]
        }
      ]
    }
  />

const TabForm = connect(
  state => ({
    editorTabs: selectors.editorTabs(state)
  })
)(TabFormComponent)

export {
  TabForm
}
