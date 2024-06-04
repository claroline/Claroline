import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/Modal'

import {asset} from '#/main/app/config/asset'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'
import {ContentTabs} from '#/main/app/content/components/tabs'

const ModalTabbed = (props) =>
  <ModalEmpty
    {...omit(props, 'closeButton', 'tabs')}
  >
    {(props.title || props.icon) &&
      <BaseModal.Header
        className="pb-0 pt-1"
        closeButton={props.closeButton}
      >
        {props.icon &&
          <span className={classes('modal-icon fs-5 mb-1 align-self-center', props.icon)} aria-hidden={true} />
        }

        <ContentTabs
          className="border-bottom-0"
          sections={props.tabs}
        />
      </BaseModal.Header>
    }

    {props.children}
  </ModalEmpty>

ModalTabbed.propTypes = {
  ...ModalEmpty.propTypes,

  icon: T.string,
  tabs: T.arrayOf(T.shape({

  })).isRequired
}

ModalTabbed.defaultProps = {
  closeButton: true
}

export {
  ModalTabbed
}
