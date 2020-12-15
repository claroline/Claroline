import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/rss/resources/rss-feed/editor/store'

// TODO : should be merged with creation form

const Editor = (props) =>
  <FormData
    level={3}
    displayLevel={2}
    name={selectors.FORM_NAME}
    buttons={true}
    target={(rssFeed) => ['apiv2_rss_feed_update', {id: rssFeed.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('url'),
        primary: true,
        fields: [
          {
            name: 'url',
            label: trans('url'),
            type: 'url',
            required: true
          }
        ]
      }
    ]}
  />

Editor.propTypes = {
  path: T.string.isRequired
}

export {
  Editor
}
