import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditor, ResourceEditorAppearance} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncementEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[{
      title: trans('Templates'),
      subtitle: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer condimentum scelerisque lorem, non finibus ligula pretium et.'),
      primary: true,
      fields: [{
        name: 'resource.templateEmail',
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
        name: 'resource.templatePdf',
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

const AnnouncementEditor = () => {
  const announcement = useSelector(selectors.announcement)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: announcement
      })}
      appearancePage={AnnouncementEditorAppearance}
    />
  )
}

export {
  AnnouncementEditor
}
