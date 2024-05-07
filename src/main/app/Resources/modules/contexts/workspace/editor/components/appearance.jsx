import React from 'react'

import {trans} from '#/main/app/intl'

import {selectors} from '#/main/app/context/editor/store/selectors'
import {ContextEditorAppearance} from '#/main/app/context/editor/components/appearance'

const WorkspaceEditorAppearance = (props) =>
  <ContextEditorAppearance
    definition={[
      {
        title: trans('advanced'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }
        ]
      }, {
        name: 'opening',
        title: trans('Ouverture'),
        subtitle: 'Lorem ipsum dolor sit amet non sequiture et cetera',
        primary: true,
        fields: [
          {
            name: 'opening.type',
            type: 'choice',
            label: trans('type'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                tool: trans('open_workspace_tool'),
                resource: trans('open_workspace_resource')
              }
            },
            onChange: () => props.updateProp('opening.target', null),
            linked: [
              {
                name: 'opening.target',
                type: 'choice',
                label: trans('tool'),
                required: true,
                displayed: (workspace) => workspace.opening && 'tool' === workspace.opening.type,
                options: {
                  noEmpty: true,
                  multiple: false,
                  condensed: true,
                  choices: props.tools ? props.tools.reduce((acc, tool) => Object.assign(acc, {
                    [tool.name]: trans(tool.name, {}, 'tools')
                  }), {}) : {}
                }
              }, {
                name: 'opening.target',
                type: 'resource',
                help: trans ('opening_target_resource_help'),
                label: trans('resource'),
                options: {
                  picker: {
                    current: props.root,
                    root: props.root
                  }
                },
                required: true,
                displayed: (workspace) => workspace.opening && 'resource' === workspace.opening.type,
                onChange: (selected) => {
                  props.updateProp('opening.target', selected)
                }
              }
            ]
          }, {
            name: 'opening.menu',
            type: 'choice',
            label: trans('tools_menu'),
            mode: 'expert',
            placeholder: trans('do_nothing'),
            options: {
              condensed: false,
              noEmpty: false,
              choices: {
                open: trans('open_tools_menu'),
                close: trans('close_tools_menu')
              }
            }
          }
        ]
      }
    ]}
  />

export {
  WorkspaceEditorAppearance
}
