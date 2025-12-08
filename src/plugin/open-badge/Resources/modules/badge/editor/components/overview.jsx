import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const BadgeEditorOverview = () => {
  return (
    <EditorPage
      title={trans('overview')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              type: 'poster',
              label: trans('poster'),
              hideLabel: true
            }, {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'image',
              type: 'image',
              label: trans('image'),
              recommended: true
            }
          ]
        }, {
          title: trans('further_information'),
          description: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'meta.description',
              type: 'string',
              label: trans('description_short'),
              help: trans('Décrivez succinctement votre badge (La description courte est affichée dans les listes et sur la vue "À propos").'),
              recommended: true,
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: 'meta.descriptionHtml',
              label: trans('description_long'),
              type: 'html',
              help: trans('Décrivez de manière détaillée le contenu de votre badge, la travail attendu par vos utilisateurs (La description détaillée est affichée sur la vue "À propos" à la place de la description courte).')
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }
          ]
        }
      ]}
    />
  )
}

export {
  BadgeEditorOverview
}
