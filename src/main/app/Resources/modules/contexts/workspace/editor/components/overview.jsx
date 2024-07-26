import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const WorkspaceEditorOverview = () =>
  <EditorPage
    title={trans('overview')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.poster',
            type: 'poster',
            label: trans('poster'),
            hideLabel: true
          }, {
            name: 'data.name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'data.code',
            type: 'string',
            label: trans('code'),
            required: true
          }
        ]
      }, {
        title: trans('further_information'),
        subtitle: trans('further_information_help'),
        primary: true,
        fields: [
          {
            name: 'data.meta.description',
            type: 'string',
            label: trans('description_short'),
            help: trans('Décrivez succintement votre espace d\'activités (La description courte est affichée dans les listes et sur la vue "À propos").'),
            options: {
              long: true,
              minRows: 2
            }
          }, {
            name: 'data.meta.descriptionHtml',
            label: trans('description_long'),
            type: 'html',
            help: trans('Décrivez de manière détaillée le contenu de votre espace d\'activités, la travail attendu par vos utilisateurs (La description détaillée est affichée sur la vue "À propos" à la place de la description courte).')
          }, {
            name: 'data.tags',
            label: trans('tags'),
            type: 'tag'
          }
        ]
      }
    ]}
  />

export {
  WorkspaceEditorOverview
}
