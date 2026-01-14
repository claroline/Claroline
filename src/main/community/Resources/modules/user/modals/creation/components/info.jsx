import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'

import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Form, FormContent} from '#/main/app/content/form'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {route} from '#/main/community/user/routing'
import {selectors} from '#/main/community/user/modals/creation/store'

const CreationInfo = (props) => {
  const history = useHistory()

  const toolPath = useSelector(toolSelectors.path)

  return (
    <Form
      name={selectors.STORE_NAME}
      flush={true}
    >
      <FormContent
        className="modal-body"
        name={selectors.STORE_NAME}
        level={2}
        displayLevel={5}
        flush={true}
        definition={[
          {
            title: trans('general'),
            fields: [
              {
                name: 'lastName',
                type: 'string',
                label: trans('last_name'),
                required: true
              }, {
                name: 'firstName',
                type: 'string',
                label: trans('first_name'),
                required: true
              }, {
                name: 'email',
                type: 'email',
                label: trans('email'),
                required: true,
                options: {
                  unique: {
                    check: ['apiv2_user_get', {field: 'email'}]
                  }
                }
              }, {
                name: 'username',
                type: 'string',
                label: trans('username'),
                required: true,
                displayed: param('community.username'),
                options: {
                  unique: {
                    check: ['apiv2_user_get', {field: 'username'}],
                    error: 'This username already exists.'
                  }
                }
              }, {
                name: 'plainPassword',
                type: 'password',
                label: trans('password'),
                required: true
              }
            ]
          }
        ]}
      />

      <div className="modal-footer">
        <Button
          type={CALLBACK_BUTTON}
          label={trans('back')}
          className="btn btn-text-body me-auto"
          callback={() => props.changeStep('start')}
        />

        <Button
          type={CALLBACK_BUTTON}
          label={trans('create_and_configure', {}, 'actions')}
          className="btn btn-link"
          callback={() => props.create().then((user) => {
            history.push(route(user, toolPath)+'/edit')
          })}
        />
        <Button
          type={CALLBACK_BUTTON}
          label={trans('create', {}, 'actions')}
          className="btn btn-primary"
          htmlType="submit"
          callback={props.create}
        />
      </div>
    </Form>
  )
}

CreationInfo.propTypes = {
  create: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  CreationInfo
}
