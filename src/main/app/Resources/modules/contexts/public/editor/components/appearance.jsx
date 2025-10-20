import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'

import {ContextEditorAppearance, selectors} from '#/main/app/context/editor'

const PublicEditorAppearance = () => {
  const enabledTools = useSelector(selectors.enabledTools)

  return (
    <ContextEditorAppearance
      definition={[
        {
          name: 'opening',
          title: trans('opening_parameters'),
          description: trans('Configurez la façon dont votre espace de travail va s\'ouvrir.'),
          primary: true,
          hideTitle: false,
          fields: [
            {
              name: 'data.opening.target',
              type: 'choice',
              label: trans('tool'),
              required: true,
              options: {
                noEmpty: true,
                multiple: false,
                condensed: true,
                choices: enabledTools ? enabledTools.reduce((acc, tool) => Object.assign(acc, {
                  [tool.name]: trans(tool.name, {}, 'tools')
                }), {}) : {}
              }
            }
          ]
        }
      ]}
    />
  )
}

export {
  PublicEditorAppearance
}
