import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {FormContent} from '#/main/app/content/form/containers/content'
import {Editor} from '#/main/app/editor/components/main'

import {selectors} from '#/main/app/context/editor/store'

const ContextTools = (props) =>
  <ul className="list-group mb-3">
    {props.availableTools
      .map((tool) =>
        <li className="list-group-item d-flex gap-3 align-items-center" key={tool.name}>
          <span className={`fs-4 fa fa-fw fa-${tool.icon}`} />
          <div className="flex-fill" role="presentation">
            <span className="fw-medium">{trans(tool.name, {}, 'tools')}</span>
            <p className="mb-0 text-secondary">{trans(tool.name+'_desc', {}, 'tools')}</p>
          </div>

          <div className="form-check form-switch align-self-start">
            <input
              id={tool.name}
              className="form-check-input"
              type="checkbox"
              checked={-1 !== props.tools.indexOf(t => t.name === tool.name)}
              disabled={tool.required}
              onChange={e => props.onChange(e.target.checked)}
            />
          </div>
        </li>
      )
    }
  </ul>

ContextTools.propTypes = {
  tools: T.arrayOf(T.shape({

  })).isRequired,
  availableTools: T.arrayOf(T.shape({
    icon: T.string,
    name: T.string.isRequired,
    required: T.bool.isRequired
  })),
  onChange: T.func.isRequired
}

const ContextEditor = (props) => {
  useEffect(() => {
    props.getAvailableTools(props.contextName, props.contextId)
    props.openEditor(props.contextData)
  }, [props.contextName, props.contextId])

  return (
    <Editor
      path={props.path+'/edit'}
      title={trans(props.contextName, {}, 'context')}
      name={selectors.FORM_NAME}
      onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
      target={['apiv2_context_configure', {
        context: props.contextName,
        contextId: props.contextId
      }]}
      close={props.path}
      overview={props.overview}
      defaultPage="overview"
      pages={[
        {
          name: 'opening',
          title: trans('Ouverture'),
          help: 'Lorem ipsum dolor sit amet non sequiture et cetera',
          render: () => (
            <FormContent
              name={selectors.FORM_NAME}
              definition={[
                {
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
          )
        }, {
          name: 'appearance',
          title: trans('appearance'),
          render: () => (<div>Apparence</div>)
        }, {
          name: 'history',
          title: trans('history'),
          render: () => (<div>History</div>)
        }
      ]}
      actions={props.actions}
    />
  )
}

ContextEditor.propTypes = {
  path: T.string.isRequired,
  contextName: T.string.isRequired,
  contextId: T.string,
  actions: T.Array,
  tools: T.arrayOf(T.shape({

  })).isRequired,
  availableTools: T.arrayOf(T.shape({
    icon: T.string,
    name: T.string.isRequired,
    required: T.bool.isRequired
  })),
  getAvailableTools: T.func.isRequired,
  openEditor: T.func.isRequired,
  refresh: T.func.isRequired
}

export {
  ContextEditor
}
