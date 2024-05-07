import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const ResourceEditorOverview = (props) =>
  <EditorPage
    title={trans('overview')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.poster',
            label: trans('poster'),
            type: 'poster',
            hideLabel: true
          }, {
            name: 'resourceNode.name',
            label: trans('name'),
            type: 'string',
            required: true,
            autoFocus: true
          }, {
            name: 'resourceNode.code',
            label: trans('code'),
            type: 'string',
            required: true
          }, {
            name: 'resourceNode.meta.published',
            label: trans('publish', {}, 'actions'),
            type: 'boolean',
            help: [
              trans('Publiez la ressource pour la rendre accessible à vos utilisateurs.', {}, 'resource'),
              trans('Temps que la ressource n\'est pas publiée, elle est uniquement accessible aux utilisateurs ayant la permission "Modifier".', {}, 'resource')
            ]
          }
        ]
      }, {
        title: trans('further_information'),
        subtitle: trans('further_information_help'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.meta.description',
            label: trans('description_short'),
            help: trans('Décrivez succintement votre ressource (La description courte est affichée dans les listes et sur la vue "À propos").'),
            type: 'string',
            options: {
              long: true,
              minRows: 2
            }
          }, {
            name: 'resourceNode.meta.descriptionHtml',
            label: trans('description_long'),
            type: 'html',
            help: trans('Décrivez de manière détaillée le contenu de votre ressource, la travail attendu par vos utilisateurs (La description détaillée est affichée sur la vue "À propos" à la place de la description courte).'),
          }, {
            name: 'resourceNode.tags',
            label: trans('tags'),
            type: 'tag'
          }
        ]
      }, {
        name: 'license',
        //icon: 'fa fa-fw fa-copyright',
        title: trans('authors_license'),
        subtitle: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sit amet tristique diam, sit amet auctor erat.'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.meta.authors',
            label: trans('authors'),
            type: 'string'
          }, {
            name: 'resourceNode.meta.license',
            label: trans('license'),
            type: 'string'
          }
        ]
      }
    ].concat(props.definition)}
  >
    {props.children}
  </EditorPage>

ResourceEditorOverview.propTypes = {
  definition: T.arrayOf(T.shape({

  })),
  children: T.any
}

ResourceEditorOverview.defaultProps = {
  definition: []
}

export {
  ResourceEditorOverview
}
