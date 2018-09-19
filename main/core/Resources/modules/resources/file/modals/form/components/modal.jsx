import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/components/data'

import {trans} from '#/main/core/translation'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {selectors} from '#/main/core/resources/file/modals/form/store'

class FileFormModal extends Component {
  componentDidMount() {
    this.props.resetForm()
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'resourceNode', 'saveEnabled', 'resetForm', 'updateProp', 'save')}
        icon="fa fa-fw fa-exchange-alt"
        title={trans('change_file', {}, 'resource')}
      >
        <FormData
          embedded={true}
          name={selectors.STORE_NAME}
          updateProp={this.props.updateProp}
          setErrors={() => {}}
          sections={[
            {
              title: trans('general'),
              primary: true,
              fields: [{
                name: 'file',
                type: 'file',
                label: trans('file'),
                required: true,
                options: {
                  autoUpload: false
                },
                onChange: (file) => {this.props.updateProp('file', file)}
              }]
            }
          ]}
        />
        <button
          className="modal-btn btn btn-primary"
          disabled={!this.props.saveEnabled || !this.props.data.file}
          onClick={() => {
            this.props.save(this.props.resourceNode, this.props.data.file)
            this.props.fadeModal()
          }}
        >
          {trans('save')}
        </button>
      </Modal>
    )
  }
}

FileFormModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  data: T.shape({
    file: T.object
  }).isRequired,
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  updateProp: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  FileFormModal
}
