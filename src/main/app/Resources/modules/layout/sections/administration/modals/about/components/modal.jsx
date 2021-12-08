import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentHtml} from '#/main/app/content/components/html'

const AboutModal = props =>
  <Modal
    {...omit(props, 'version', 'changelogs', 'get')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.version}
    onEntering={() => props.get()}
  >
    {props.changelogs &&
      <ContentHtml className="modal-body">
        {props.changelogs}
      </ContentHtml>
    }
  </Modal>

AboutModal.propTypes = {
  version: T.string,
  changelogs: T.string,
  get: T.func.isRequired
}

export {
  AboutModal
}
