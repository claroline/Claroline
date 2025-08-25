import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Html} from '#/main/app/components/html'
import {TextSkeleton} from '#/main/app/components/placeholder'

const AboutModal = props =>
  <Modal
    {...omit(props, 'version', 'changelogs', 'get')}
    title={trans('about')}
    subtitle={props.version}
    onEntering={props.get}
  >
    {props.changelogs ?
      <Html className="modal-body">
        {props.changelogs}
      </Html> :
      <div className="modal-body placeholder-glow">
        <TextSkeleton rows={4} />
        <TextSkeleton rows={5} />
        <TextSkeleton rows={3} />
      </div>
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
