import React from 'react'

import {t, trans} from '#/main/core/translation'
import {DataDetailsContainer} from '#/main/core/data/details/containers/details.jsx'

const Player = () =>
  <DataDetailsContainer
    level={3}
    name="bookReference"
    sections={[
      {
        id: 'general',
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'author',
            type: 'string',
            label: trans('author', {}, 'icap_bibliography')
          }, {
            name: 'isbn',
            type: 'string',
            label: trans('isbn', {}, 'icap_bibliography')
          }, {
            name: 'abstract',
            type: 'string',
            label: trans('abstract', {}, 'icap_bibliography')
          }, {
            name: 'publisher',
            type: 'string',
            label: trans('publisher', {}, 'icap_bibliography')
          }, {
            name: 'printer',
            type: 'string',
            label: trans('printer', {}, 'icap_bibliography')
          }, {
            name: 'publicationYear',
            type: 'number',
            label: trans('publication_year', {}, 'icap_bibliography')
          }, {
            name: 'language',
            type: 'string',
            label: trans('language', {}, 'icap_bibliography')
          }, {
            name: 'pages',
            type: 'number',
            label: trans('page_count', {}, 'icap_bibliography')
          }, {
            name: 'url',
            type: 'string',
            label: trans('url', {}, 'icap_bibliography')
          }, {
            name: 'cover',
            type: 'string',
            label: trans('cover_url', {}, 'icap_bibliography')
          }
        ]
      }
    ]}
  />

export {
  Player
}
