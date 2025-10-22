import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const EditorModal = props =>
  <FormModal
    {...omit(props, 'tos')}
    icon="fa fa-fw fa-file-shield"
    title={trans('terms_of_service',{},'privacy')}
    name="termsOfServiceForm"
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
            linked: [
              {
                name: 'tos.template',
                label: trans('terms_of_service', {}, 'template'),
                type: 'template',
                displayed: get(props, 'tos.enabled'),
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

EditorModal.propTypes = {
  tos: T.shape({
    enabled: T.bool,
    template: T.object
  })
}

export {
  EditorModal
}
