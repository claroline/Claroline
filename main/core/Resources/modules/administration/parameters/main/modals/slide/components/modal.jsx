import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

const SlideFormModal = props =>
  <Modal
    {...omit(props, 'formName', 'dataPart')}
    title={props.title}
  >
    <FormData
      level={5}
      name={props.formName}
      dataPart={props.dataPart}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              label: trans('title'),
              type: 'string',
              required: false
            }, {
              name: 'picture',
              label: trans('image'),
              type: 'file',
              options: {
                types: ['image/*']
              },
              required: false
            }, {
              name: 'content',
              label: trans('content'),
              type: 'html',
              required: false
            }
          ]
        }
      ]}
    />
  </Modal>

SlideFormModal.propTypes = {
  formName: T.string.isRequired,
  dataPart: T.string.isRequired,
  title: T.string.isRequired
}

SlideFormModal.defaultProps = {
  title: trans('content_edition')
}

export {
  SlideFormModal
}
