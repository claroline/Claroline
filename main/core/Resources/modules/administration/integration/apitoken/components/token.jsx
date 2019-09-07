import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Token = (props) =>
  <FormData
    level={2}
    name="api_tokens.token"
    target={(token, isNew) => isNew ?
      ['apiv2_apitoken_create'] :
      ['apiv2_apitoken_update', {id: token.id}]
    }
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/tokens`,
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
            options: {
              long: true
            }
          }, {
            name: 'user',
            type: 'user',
            label: trans('user'),
            required: true
          }
        ]
      }
    ]}
  />

Token.propTypes = {
  path: T.string.isRequired
}

export {
  Token
}
