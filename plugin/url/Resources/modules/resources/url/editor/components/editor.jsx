import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/plugin/url/resources/url/editor/store'
import {LINK_BUTTON} from '#/main/app/buttons'

const UrlForm = props =>
  <FormData
    level={5}
    name={selectors.FORM_NAME}
    target={['apiv2_url_update', {id: props.url.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
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

UrlForm.propTypes = {
  url: T.shape({
    'id': T.number.isRequired
  }).isRequired
}

const Editor = connect(
  (state) => ({
    url: selectors.url(state)
  })
)(UrlForm)

export {
  Editor
}
