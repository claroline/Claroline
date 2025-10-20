import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'

import {ContextEditorAppearance, actions, selectors} from '#/main/app/context/editor'

const WorkspaceEditorAppearance = () => {
  const context = useSelector(selectors.contextData)
  const enabledTools = useSelector(selectors.enabledTools)

  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(actions.update(value, 'data.'+prop))
  }

  return (
    <ContextEditorAppearance
      definition={[
        {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          primary: true,
          fields: [
            {
              name: 'data.thumbnail',
              label: trans('thumbnail'),
              type: 'image'
            }
          ]
        }, {
          title: trans('advanced'),
          primary: true,
          hideTitle: true,
          fields: [
            {
              name: 'data.restrictions.hidden',
              type: 'boolean',
              label: trans('restrict_hidden'),
              help: trans('restrict_hidden_help')
            }
          ]
        }, {
          name: 'opening',
          title: trans('opening_parameters'),
          description: trans('Configurez la façon dont votre espace de travail va s\'ouvrir.'),
          primary: true,
          fields: [
            {
              name: 'data.opening.type',
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
              onChange: () => updateProp('opening.target', null),
              linked: [
                {
                  name: 'data.opening.target',
                  type: 'choice',
                  label: trans('tool'),
                  required: true,
                  displayed: (formData) => 'tool' === get(formData, 'data.opening.type'),
                  options: {
                    noEmpty: true,
                    multiple: false,
                    condensed: true,
                    choices: enabledTools ? enabledTools.reduce((acc, tool) => Object.assign(acc, {
                      [tool.name]: trans(tool.name, {}, 'tools')
                    }), {}) : {}
                  }
                }, {
                  name: 'data.opening.target',
                  type: 'resource',
                  help: trans ('opening_target_resource_help'),
                  label: trans('resource'),
                  options: {
                    picker: {
                      contextId: context ? context.id : null
                    }
                  },
                  required: true,
                  displayed: (formData) => 'resource' === get(formData, 'data.opening.type'),
                  onChange: (selected) => updateProp('opening.target', selected)
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  WorkspaceEditorAppearance
}
