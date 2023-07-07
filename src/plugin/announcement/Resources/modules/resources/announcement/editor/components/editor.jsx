import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/announcement/resources/announcement/editor/store'

const AnnouncesEditor = props =>
  <FormData
    name={selectors.FORM_NAME}
    title={trans('parameters')}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.announcement.id)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[{
      title: trans('general'),
      primary: true,
      fields: [{
        name: 'templateEmail',
        label: trans('email_announcement', {}, 'template'),
        type: 'template',
        options: {
          picker: {
            filters: [{
              property: 'typeName',
              value: 'email_announcement',
              locked: true
            }]
          }
        }
      }, {
        name: 'templatePdf',
        label: trans('pdf_announcement', {}, 'template'),
        type: 'template',
        options: {
          picker: {
            filters: [{
              property: 'typeName',
              value: 'pdf_announcement',
              locked: true
            }]
          }
        }
      }]
    }]}
  />

export {
  AnnouncesEditor
}
