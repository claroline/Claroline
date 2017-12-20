import React from 'react'

import {t, trans} from '#/main/core/translation'
import {FormContainer as Form} from '#/main/core/data/form/containers/form.jsx'
import {makeSaveAction} from '#/main/core/data/form/containers/form-save.jsx'
import {PageAction} from '#/main/core/layout/page/components/page-actions.jsx'

const BookReferenceSaveAction = makeSaveAction('bookReferenceForm', formData => ({
  update: ['apiv2_book_reference_update', {id: formData.id}]
}))(PageAction)

const Editor = () =>
  <Form
    level={3}
    name="bookReferenceForm"
    sections={[
      {
        id: 'general',
        title: t('general'),
        primary: true,
        fields: [
          {name: 'author', type: 'string', label: trans('author', {}, 'icap_bibliography'), required: true},
          {name: 'isbn', type: 'string', label: trans('isbn', {}, 'icap_bibliography'), required: true},
          {name: 'abstract', type: 'string', label: trans('abstract', {}, 'icap_bibliography'), required: false},
          {name: 'publisher', type: 'string', label: trans('publisher', {}, 'icap_bibliography'), required: false},
          {name: 'printer', type: 'string', label: trans('printer', {}, 'icap_bibliography'), required: false},
          {name: 'publicationYear', type: 'number', label: trans('publicationYear', {}, 'icap_bibliography'), required: false},
          {name: 'language', type: 'string', label: trans('language', {}, 'icap_bibliography'), required: false},
          {name: 'pages', type: 'number', label: trans('pages', {}, 'icap_bibliography'), required: false},
          {name: 'url', type: 'string', label: trans('url', {}, 'icap_bibliography'), required: false},
          {name: 'cover', type: 'string', label: trans('cover', {}, 'icap_bibliography'), required: false}
        ]
      }
    ]}
  >
    <BookReferenceSaveAction/>
  </Form>

export {
  Editor
}
