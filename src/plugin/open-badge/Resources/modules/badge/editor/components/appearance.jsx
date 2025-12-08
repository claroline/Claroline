import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const BadgeEditorAppearance = (props) =>
  <EditorPage
    title={trans('appearance')}
    help={trans('Personnalisez les paramètres d\'affichage avancés de votre badge.')}
    definition={[
      {
        title: trans('display_parameters'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'template',
            label: trans('badge_certificate', {}, 'template'),
            type: 'template',
            options: {
              templateType: 'badge_certificate'
            }
          }
        ]
      }
    ].concat(props.definition || [])}
  />

export {
  BadgeEditorAppearance
}
