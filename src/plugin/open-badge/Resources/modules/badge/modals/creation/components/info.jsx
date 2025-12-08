import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Form, FormContent} from '#/main/app/content/form'
import {route as toolRoute} from '#/main/core/tool/routing'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {selectors} from '#/plugin/open-badge/badge/modals/creation/store'
import {route} from '#/plugin/open-badge/badge/routing'

const CreationInfo = (props) => {
  const history = useHistory()
  const contextPath = useSelector(contextSelectors.path)

  return (
    <Form
      name={selectors.STORE_NAME}
      flush={true}
      level={2}
      displayLevel={5}
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
                name: 'poster',
                type: 'poster',
                label: trans('poster'),
                hideLabel: true
              }, {
                name: 'name',
                type: 'string',
                label: trans('name'),
                required: true,
                autoFocus: true
              }, {
                name: 'image',
                type: 'image',
                label: trans('image'),
                recommended: true
              }, {
                name: 'meta.description',
                type: 'string',
                label: trans('description_short'),
                help: trans('Décrivez succinctement votre badge (La description courte est affichée dans les listes et sur la vue "À propos").'),
                options: {
                  long: true,
                  minRows: 2
                }
              }
            ]
          }
        ]}
      />

      <div className="modal-footer mt-n5">
        <Button
          type={CALLBACK_BUTTON}
          label={trans('back')}
          className="btn btn-text-body me-auto"
          callback={() => props.changeStep('type')}
        />

        <Button
          type={CALLBACK_BUTTON}
          label={trans('create_and_configure', {}, 'actions')}
          className="btn btn-link"
          callback={() => props.create().then((badge) => {
            history.push(route(badge, toolRoute('badges', contextPath))+'/edit')
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
