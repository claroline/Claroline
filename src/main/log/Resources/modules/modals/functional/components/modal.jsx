import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {useReducer} from '#/main/app/store/reducer'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays'
import {makeListReducer} from '#/main/app/content/list'

import {LogFunctionalList} from '#/main/log/components/functional-list'

const STORE_NAME = 'functionalLogsModal'

const FunctionalLogsModal = (props) => {
  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: {property: 'date', direction: -1}
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  return (
    <Modal
      title={trans('actions')}
      {...omit(props, 'url')}
      className="data-picker-modal"
      centered={true}
      scrollable={true}
      enforceFocus={true}
    >
      <div className="modal-body p-0 d-flex">
        <LogFunctionalList
          className="border-top"
          autoFocus={true}
          flush={true}
          name={STORE_NAME}
          url={props.url}
        />
      </div>
    </Modal>
  )
}

FunctionalLogsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired
}

export {
  FunctionalLogsModal
}
