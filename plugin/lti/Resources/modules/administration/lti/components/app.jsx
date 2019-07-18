import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const App = (props) => {
  return(
    <FormData
      level={2}
      name="lti.app"
      target={(app, isNew) => isNew ?
        ['apiv2_lti_create'] :
        ['apiv2_lti_update', {id: app.id}]
      }
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: `${props.path}/lti`,
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }, {
              name: 'url',
              type: 'string',
              label: trans('url', {}, 'lti'),
              required: true
            }, {
              name: 'appKey',
              type: 'string',
              label: trans('key', {}, 'lti'),
              required: false
            }, {
              name: 'secret',
              type: 'string',
              label: trans('secret', {}, 'lti'),
              required: false
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              required: false
            }
          ]
        }
      ]}
    />
  )}

App.propTypes = {
  path: T.string.isRequired
}

export {
  App
}
