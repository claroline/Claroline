import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {FormData} from '#/main/app/content/form/containers/data'

import {ContextPage} from '#/main/app/context/containers/page'
import {selectors} from '#/main/app/context/editor/store'
import {LINK_BUTTON} from '#/main/app/buttons'

const ContextEditor = (props) =>
  <ContextPage
    title={trans('parameters')}
  >
    <FormData
      className="mt-3"
      name={selectors.STORE_NAME}
      buttons={true}
      definition={[
        {
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
            }, {
              name: 'breadcrumb.displayed',
              type: 'boolean',
              label: trans('showBreadcrumbs'),
              displayed: true,
              mode: 'advanced',
              linked: [
                {
                  name: 'breadcrumb.items',
                  type: 'choice',
                  label: trans('links'),
                  required: true,
                  displayed: (workspace) => get(workspace, 'breadcrumb.displayed') || false,
                  options: {
                    choices: {
                      desktop: trans('desktop'),
                      workspaces: trans('workspace_list'),
                      current: trans('current_workspace'),
                      tool: trans('tool')
                    },
                    inline: false,
                    condensed: false,
                    multiple: true
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-sign-in',
          title: trans('opening_parameters'),
          mode: 'advanced',
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
  </ContextPage>

ContextEditor.propTypes = {
  tools: T.arrayOf(T.shape({

  })).isRequired,
  availableTools: T.arrayOf(T.shape({

  }))
}

export {
  ContextEditor
}
