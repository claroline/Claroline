import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/core/tools/parameters/store/selectors'

const TokenForm = (props) =>
  <FormData
    level={3}
    name={`${selectors.STORE_NAME}.tokens.current`}
    buttons={true}
    target={(token) => ['apiv2_apitoken_update', {id: token.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path + '/tokens',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'description',
            type: 'string',
            label: trans('description'),
            required: true
          },
          {
            name: 'token',
            type: 'string',
            label: trans('token'),
            required: true,
            disabled: true
          }
        ]
      }
    ]}
  />

TokenForm.propTypes = {
  path: T.string.isRequired
}

const Token = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(TokenForm)

export {
  Token
}
