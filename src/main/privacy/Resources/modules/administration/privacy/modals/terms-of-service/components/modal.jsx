import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {actions} from '#/main/app/content/form'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const FORM_NAME = 'termsOfServiceForm'

const EditorModal = props => {
  const dispatch = useDispatch()

  const update = useCallback((propKey, propValue) => {
    dispatch(actions.updateProp(FORM_NAME, propKey, propValue))
  }, [FORM_NAME])

  return (
    <FormModal
      {...omit(props, 'tos')}
      icon="fa fa-fw fa-file-shield"
      title={trans('terms_of_service',{},'privacy')}
      name={FORM_NAME}
      isNew={false}
      target={['apiv2_privacy_update']}
      data={{tos: props.tos}}
      definition={[
        {
          title: trans('terms_of_service', {}, 'privacy'),
          fields: [
            {
              name: 'tos.enabled',
              type: 'boolean',
              label: trans('terms_of_service_activation', {}, 'privacy'),
              help: trans('terms_of_service_activation_help', {}, 'privacy'),
              onChange: (enabled) => {
                if (!enabled) {
                  update('tos.template', null)
                }
              },
              linked: [
                {
                  name: 'tos.template',
                  label: trans('terms_of_service', {}, 'template'),
                  type: 'template',
                  displayed: (data) => get(data, 'tos.enabled'),
                  options: {
                    templateType: 'terms_of_service'
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

EditorModal.propTypes = {
  tos: T.shape({
    enabled: T.bool,
    template: T.object
  })
}

export {
  EditorModal
}
