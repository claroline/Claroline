import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {LINK_BUTTON} from '#/main/app/buttons'



const TokenForm = () =>
  <FormData
    level={3}
    name="tokens.current"
    buttons={true}
    target={(token) => ['apiv2_apitoken_update', {id: token.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: '/tokens',
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
  >
  </FormData>

TokenForm.propTypes = {
  new: T.bool.isRequired,
  token: T.shape({
    id: T.string
  }).isRequired
}

const Token = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'tokens.current')),
    token: formSelect.data(formSelect.form(state, 'tokens.current'))
  }),
  () => ({
  })
)(TokenForm)

export {
  Token
}
