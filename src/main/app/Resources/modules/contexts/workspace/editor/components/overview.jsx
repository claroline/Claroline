import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {useSelector} from 'react-redux'
import {selectors} from '#/main/app/context/editor'
import {EditorOverview} from '#/main/app/editor/components/overview'

const WorkspaceEditorOverview = () => {
  const context = useSelector(selectors.contextData)

  return (
    <EditorOverview
      meta={{
        id: get(context, 'id'),
        createdAt: get(context, 'meta.created'),
        updatedAt: get(context, 'meta.updated')
      }}
      dataPart="data"
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
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
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
              help: trans('Décrivez succinctement votre espace d\'activités (La description courte est affichée dans les listes et sur la vue "À propos").'),
              recommended: true,
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: 'meta.descriptionHtml',
              label: trans('description_long'),
              type: 'html',
              help: trans('Décrivez de manière détaillée le contenu de votre espace d\'activités, la travail attendu par vos utilisateurs (La description détaillée est affichée sur la vue "À propos" à la place de la description courte).')
            }, {
              name: 'contactEmail',
              label: trans('contact_email'),
              type: 'email'
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
  WorkspaceEditorOverview
}
