import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'

import {UrlForm} from '#/plugin/url/components/form'
import {selectors} from '#/plugin/url/resources/url/editor/store'

const Editor = props =>
  <UrlForm
    level={5}
    name={selectors.FORM_NAME}
    target={['apiv2_url_update', {id: props.url.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    url={props.url}
    updateProp={props.updateProp}
  />

Editor.propTypes = {
  path: T.string.isRequired,
  url: T.shape({
    id: T.number.isRequired,
    mode: T.string,
    placeholders: T.arrayOf(T.string)
  }).isRequired,
  updateProp: T.func.isRequired
}

export {
  Editor
}
