import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CreationType} from '#/plugin/cursus/course/components/type'

const CreationModal = (props) =>
  <Modal
    {...omit(props, 'openForm')}
    title={trans('new_course', {}, 'cursus')}
    subtitle={trans('new_course_desc', {}, 'cursus')}
    centered={true}
    onExited={props.reset}
  >
    <div className="modal-body">
      <CreationType
        path={props.path}
        contextType={props.contextType}
        contextId={props.contextId}
        openForm={props.openForm}
        fadeModal={props.fadeModal}
        modal={true}
        onCreate={props.onCreate}
      />
    </div>
  </Modal>

CreationModal.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  onCreate: T.func,
  openForm: T.func.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CreationModal
}
