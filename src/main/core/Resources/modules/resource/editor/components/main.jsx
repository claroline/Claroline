import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/core/resource/store'
import {ResourcePage} from '#/main/core/resource/components/page'

const ResourceEditor = (props) => {
  const resourcePath = useSelector(selectors.path)

  return (
    <ResourcePage
      title={trans('parameters')}
      actions={[
        {
          name: 'edit-poster',
          type: CALLBACK_BUTTON,
          label: trans('Modifier la couverture'),
          callback: () => true
        }
      ]}
      menu={{
        nav: [
          {
            name: 'overview',
            label: trans('about'),
            type: LINK_BUTTON,
            target: resourcePath+'/edit',
            exact: true
          }, {
            name: 'permissions',
            label: trans('permissions'),
            type: LINK_BUTTON,
            target: resourcePath+'/edit/permissions'
          }, {
            name: 'history',
            label: trans('history'),
            type: LINK_BUTTON,
            target: resourcePath+'/edit/history'
          }
        ],
        actions: [
          {
            name: 'close',
            label: trans('close'),
            icon: 'fa far fa-fw fa-times-circle',
            type: LINK_BUTTON,
            target: resourcePath,
            exact: true
          }
        ]
      }}
    >
      <div>Editor</div>
    </ResourcePage>
  )
}

export {
  ResourceEditor
}
