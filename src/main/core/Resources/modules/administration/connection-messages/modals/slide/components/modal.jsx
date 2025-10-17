import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {User as UserTypes} from '#/main/community/prop-types'

class SlideFormModal extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'formName', 'dataPart')}
        title={this.props.title}
        size="lg"
      >
        <FormData
          level={5}
          name={this.props.formName}
          dataPart={this.props.dataPart}
          flush={true}
          definition={[
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
                  name: 'poster',
                  label: trans('image'),
                  type: 'image',
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
    )
  }
}

SlideFormModal.propTypes = {
  formName: T.string.isRequired,
  dataPart: T.string.isRequired,
  title: T.string.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  )
}

SlideFormModal.defaultProps = {
  title: trans('content_edition')
}

export {
  SlideFormModal
}
