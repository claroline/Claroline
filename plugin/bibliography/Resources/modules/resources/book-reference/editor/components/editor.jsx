import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/bibliography/resources/book-reference/store'

const Editor = () =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME+'.bookReference'}
    target={(bookReference) => ['apiv2_book_reference_update', {id: bookReference.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'author',
            type: 'string',
            label: trans('author', {}, 'icap_bibliography'),
            required: true
          }, {
            name: 'isbn',
            type: 'string',
            label: trans('isbn', {}, 'icap_bibliography'),
            required: true
          }, {
            name: 'abstract',
            type: 'string',
            label: trans('abstract', {}, 'icap_bibliography'),
            required: false,
            options: {
              long: true
            }
          }, {
            name: 'publisher',
            type: 'string',
            label: trans('publisher', {}, 'icap_bibliography'),
            required: false
          }, {
            name: 'printer',
            type: 'string',
            label: trans('printer', {}, 'icap_bibliography'),
            required: false
          }, {
            name: 'publicationYear',
            type: 'number',
            label: trans('publication_year', {}, 'icap_bibliography'),
            required: false
          }, {
            name: 'language',
            type: 'string',
            label: trans('language', {}, 'icap_bibliography'),
            required: false
          }, {
            name: 'pages',
            type: 'number',
            label: trans('page_count', {}, 'icap_bibliography'),
            required: false,
            options: {
              min: 0
            }
          }, {
            name: 'url',
            type: 'string',
            label: trans('url', {}, 'icap_bibliography'),
            required: false
          }, {
            name: 'cover',
            type: 'string',
            label: trans('cover_url', {}, 'icap_bibliography'),
            required: false
          }
        ]
      }
    ]}
  />

export {
  Editor
}
